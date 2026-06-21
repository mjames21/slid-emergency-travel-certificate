<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Support\Audit;
use App\Support\DocumentHashService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecordNraReceiptPaymentService
{
    public function __construct(protected DocumentHashService $documentHashService)
    {
    }

    public function handle(User $user, Invoice $invoice, array $payload): Receipt
    {
        $receiptNo = $this->normalizeReceiptNo((string) ($payload['receipt_no'] ?? ''));

        if ($receiptNo === '') {
            throw ValidationException::withMessages([
                'manual_receipt_no' => 'Enter the NRA receipt number printed on the receipt.',
            ]);
        }

        return DB::transaction(function () use ($user, $invoice, $payload, $receiptNo) {
            $invoice = Invoice::query()
                ->with(['visaApplication', 'payments.receipt'])
                ->lockForUpdate()
                ->findOrFail($invoice->id);

            if ($invoice->status === InvoiceStatus::Paid) {
                $receipt = $invoice->payments->first(fn (Payment $payment) => $payment->receipt !== null)?->receipt;

                if ($receipt) {
                    return $receipt;
                }
            }

            $receiptExists = Receipt::query()
                ->where('receipt_no', $receiptNo)
                ->exists();

            if ($receiptExists) {
                throw ValidationException::withMessages([
                    'manual_receipt_no' => 'This receipt number has already been used. A receipt cannot be recycled for another traveler.',
                ]);
            }

            $paidAt = now();
            $evidenceHash = (string) ($payload['evidence_hash'] ?? '');
            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'confirmed_by' => $user->id,
                'gateway' => 'nra_manual',
                'gateway_transaction_id' => 'NRA-' . $receiptNo,
                'gateway_reference' => $invoice->payment_reference,
                'payment_channel' => 'nra_receipt',
                'amount_due' => $invoice->amount,
                'amount_paid' => $invoice->amount,
                'currency' => $invoice->currency,
                'status' => PaymentStatus::Successful,
                'raw_payload' => [
                    'source' => 'nra_receipt',
                    'receipt_no' => $receiptNo,
                    'evidence_path' => $payload['evidence_path'] ?? null,
                    'evidence_hash' => $evidenceHash ?: null,
                    'note' => $payload['note'] ?? null,
                ],
                'verification_payload' => [
                    'verified_by_user_id' => $user->id,
                    'verified_by' => $user->name,
                    'verified_at' => $paidAt->toIso8601String(),
                    'receipt_no' => $receiptNo,
                    'evidence_hash' => $evidenceHash ?: null,
                ],
                'initiated_at' => $paidAt,
                'paid_at' => $paidAt,
                'verified_at' => $paidAt,
            ]);

            $receipt = Receipt::query()->create([
                'receipt_no' => $receiptNo,
                'payment_id' => $payment->id,
                'issued_by' => $user->id,
                'receipt_source' => 'nra_manual',
                'evidence_path' => $payload['evidence_path'] ?? null,
                'evidence_original_name' => $payload['evidence_original_name'] ?? null,
                'evidence_mime_type' => $payload['evidence_mime_type'] ?? null,
                'evidence_size' => $payload['evidence_size'] ?? null,
                'evidence_hash' => $evidenceHash ?: null,
                'notes' => $payload['note'] ?? null,
                'document_hash' => $this->documentHashService->generate(implode(':', [
                    'nra-receipt',
                    $receiptNo,
                    $invoice->invoice_no,
                    $evidenceHash,
                    $paidAt->toIso8601String(),
                ])),
                'issued_at' => $paidAt,
            ]);

            $invoice->update([
                'gateway' => 'nra_manual',
                'status' => InvoiceStatus::Paid,
                'paid_at' => $paidAt,
            ]);

            $invoice->visaApplication?->update([
                'status' => VisaApplicationStatus::Paid,
                'last_status_changed_at' => $paidAt,
            ]);

            Audit::log(
                action: 'payment.nra_receipt_confirmed',
                description: 'NRA receipt uploaded and payment confirmed.',
                auditable: $payment,
                metadata: [
                    'invoice_no' => $invoice->invoice_no,
                    'receipt_no' => $receiptNo,
                    'payment_id' => $payment->id,
                    'receipt_id' => $receipt->id,
                    'evidence_hash' => $evidenceHash ?: null,
                ]
            );

            return $receipt;
        });
    }

    private function normalizeReceiptNo(string $receiptNo): string
    {
        $receiptNo = preg_replace('/\s+/', ' ', trim($receiptNo)) ?: '';

        return strtoupper($receiptNo);
    }
}
