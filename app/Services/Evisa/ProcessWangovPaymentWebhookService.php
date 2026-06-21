<?php

namespace App\Services\Evisa;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessWangovPaymentWebhookService
{
    public function handle(
        string $reference,
        string $status,
        array $payload,
        ?string $providerReference = null,
        ?float $amount = null,
        ?string $currency = null,
        ?Carbon $eventTime = null
    ): array {
        return DB::transaction(function () use ($reference, $status, $payload, $providerReference, $amount, $currency, $eventTime) {
            $invoice = Invoice::query()
                ->with(['visaApplication', 'payments'])
                ->where('payment_reference', $reference)
                ->lockForUpdate()
                ->first();

            if (! $invoice) {
                return ['ok' => true, 'handled' => false, 'reason' => 'unknown_payment_reference'];
            }

            $paidStatuses = ['paid', 'success', 'successful', 'completed', 'complete', 'confirmed', 'full'];
            $failedStatuses = ['failed', 'declined', 'cancelled', 'canceled', 'error', 'expired'];
            $reversedStatuses = ['reversed', 'refund', 'refunded', 'chargeback'];
            $status = strtolower($status);

            if (in_array($status, $paidStatuses, true)) {
                if (! $this->paymentMatchesInvoice($invoice, $amount, $currency)) {
                    $this->pendingPayment($invoice, $payload, $providerReference);

                    return [
                        'ok' => true,
                        'handled' => true,
                        'type' => 'visa_invoice',
                        'action' => 'payment_mismatch_requires_review',
                    ];
                }

                $payment = $this->successfulPayment($invoice, $payload, $providerReference, $amount, $currency, $eventTime);

                $invoice->update([
                    'status' => InvoiceStatus::Paid,
                    'paid_at' => $eventTime ?: now(),
                ]);

                $invoice->visaApplication?->update([
                    'status' => VisaApplicationStatus::Paid,
                    'online_payment_returned_at' => $eventTime ?: now(),
                    'last_status_changed_at' => now(),
                ]);

                return [
                    'ok' => true,
                    'handled' => true,
                    'type' => 'visa_invoice',
                    'action' => 'marked_paid',
                    'payment_id' => $payment->id,
                ];
            }

            if (in_array($status, $reversedStatuses, true)) {
                $invoice->payments()
                    ->where('status', PaymentStatus::Successful)
                    ->update([
                        'status' => PaymentStatus::Reversed,
                        'verification_payload' => $payload,
                    ]);

                $invoice->update([
                    'status' => InvoiceStatus::Failed,
                    'paid_at' => null,
                ]);

                $invoice->visaApplication?->update([
                    'status' => VisaApplicationStatus::AwaitingPayment,
                    'last_status_changed_at' => now(),
                ]);

                return ['ok' => true, 'handled' => true, 'type' => 'visa_invoice', 'action' => 'reversed'];
            }

            if (in_array($status, $failedStatuses, true)) {
                $invoice->payments()
                    ->where('status', PaymentStatus::Pending)
                    ->update([
                        'status' => PaymentStatus::Failed,
                        'failed_at' => now(),
                        'failure_reason' => $status,
                        'verification_payload' => $payload,
                    ]);

                $invoice->update([
                    'status' => $status === 'expired' ? InvoiceStatus::Expired : InvoiceStatus::Failed,
                ]);

                $invoice->visaApplication?->update([
                    'status' => VisaApplicationStatus::AwaitingPayment,
                    'last_status_changed_at' => now(),
                ]);

                return ['ok' => true, 'handled' => true, 'type' => 'visa_invoice', 'action' => 'failed'];
            }

            $this->pendingPayment($invoice, $payload, $providerReference);

            return ['ok' => true, 'handled' => true, 'type' => 'visa_invoice', 'action' => 'meta_only'];
        });
    }

    private function successfulPayment(
        Invoice $invoice,
        array $payload,
        ?string $providerReference,
        ?float $amount,
        ?string $currency,
        ?Carbon $eventTime
    ): Payment {
        $existing = $invoice->payments()
            ->where('status', PaymentStatus::Successful)
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        $payment = $invoice->payments()
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();

        $data = [
            'gateway' => 'wangov',
            'gateway_transaction_id' => $providerReference ?: null,
            'gateway_reference' => $invoice->payment_reference,
            'payment_channel' => 'wangov_checkout',
            'amount_due' => $invoice->amount,
            'amount_paid' => $amount ?? $invoice->amount,
            'currency' => $currency ?: $invoice->currency,
            'status' => PaymentStatus::Successful,
            'raw_payload' => $payment?->raw_payload ?? [],
            'verification_payload' => $payload,
            'initiated_at' => $payment?->initiated_at ?: now(),
            'paid_at' => $eventTime ?: now(),
            'verified_at' => now(),
            'failed_at' => null,
            'failure_reason' => null,
        ];

        if ($payment) {
            $payment->update($data);

            return $payment;
        }

        return Payment::query()->create([
            'invoice_id' => $invoice->id,
            'confirmed_by' => null,
            ...$data,
        ]);
    }

    private function paymentMatchesInvoice(Invoice $invoice, ?float $amount, ?string $currency): bool
    {
        if ($amount !== null && abs(round($amount, 2) - round((float) $invoice->amount, 2)) > 0.009) {
            return false;
        }

        if ($currency !== null && strtoupper($currency) !== strtoupper((string) $invoice->currency)) {
            return false;
        }

        return true;
    }

    private function pendingPayment(Invoice $invoice, array $payload, ?string $providerReference): Payment
    {
        $payment = $invoice->payments()
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();

        if ($payment) {
            $payment->update([
                'verification_payload' => $payload,
                'gateway_transaction_id' => $payment->gateway_transaction_id ?: $providerReference,
            ]);

            return $payment;
        }

        return Payment::query()->create([
            'invoice_id' => $invoice->id,
            'confirmed_by' => null,
            'gateway' => 'wangov',
            'gateway_transaction_id' => $providerReference ?: null,
            'gateway_reference' => $invoice->payment_reference,
            'payment_channel' => 'wangov_checkout',
            'amount_due' => $invoice->amount,
            'amount_paid' => 0,
            'currency' => $invoice->currency,
            'status' => PaymentStatus::Pending,
            'raw_payload' => [],
            'verification_payload' => $payload,
            'initiated_at' => now(),
        ]);
    }
}
