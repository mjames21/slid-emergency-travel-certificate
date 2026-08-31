<?php

namespace App\Services\Evisa;

use App\Enums\PaymentStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\VisaApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RecordOnlineEvisaPaymentService
{
    public function handle(VisaApplication $application, array $payload = []): Payment
    {
        return DB::transaction(function () use ($application, $payload) {
            $lockedApplication = VisaApplication::query()
                ->with('permit')
                ->lockForUpdate()
                ->findOrFail($application->id);
            $invoiceId = $lockedApplication->latestInvoice()->value('id');
            $invoice = $invoiceId
                ? Invoice::query()->lockForUpdate()->find($invoiceId)
                : null;

            if (! $invoice) {
                throw new \RuntimeException('No invoice is available for this Emergency Travel Certificate application.');
            }

            if ($lockedApplication->permit) {
                throw new \RuntimeException('Payment cannot be changed after the Emergency Travel Certificate has been issued.');
            }

            $existing = $invoice->payments()
                ->where('status', PaymentStatus::Successful)
                ->latest()
                ->first();

            if ($existing) {
                return $existing;
            }

            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'confirmed_by' => $payload['confirmed_by'] ?? $payload['recorded_by'] ?? null,
                'gateway' => $payload['gateway'] ?? 'online_etc',
                'gateway_transaction_id' => $payload['gateway_transaction_id'] ?? 'ETC-'.Str::uuid(),
                'gateway_reference' => $payload['gateway_reference'] ?? $invoice->payment_reference,
                'payment_channel' => $payload['payment_channel'] ?? 'online',
                'amount_due' => $invoice->amount,
                'amount_paid' => $payload['amount_paid'] ?? $invoice->amount,
                'currency' => $invoice->currency,
                'status' => PaymentStatus::Successful,
                'raw_payload' => $payload,
                'verification_payload' => $payload,
                'initiated_at' => now()->subMinute(),
                'paid_at' => now(),
                'verified_at' => now(),
            ]);

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $lockedApplication->update([
                'status' => VisaApplicationStatus::Paid,
                'online_payment_returned_at' => now(),
                'last_status_changed_at' => now(),
            ]);

            return $payment;
        });
    }
}
