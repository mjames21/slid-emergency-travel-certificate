<?php

namespace App\Services\Billing;

use App\Enums\PaymentStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Support\Facades\DB;

class ConfirmPaymentService
{
    public function handle(User $user, Invoice $invoice, array $payload): Payment
    {
        return DB::transaction(function () use ($user, $invoice, $payload) {
            $payment = Payment::query()->create([
                'invoice_id' => $invoice->id,
                'confirmed_by' => $user->id,
                'gateway' => $payload['gateway'] ?? 'wangov',
                'gateway_transaction_id' => $payload['gateway_transaction_id'] ?? null,
                'gateway_reference' => $payload['gateway_reference'] ?? $invoice->payment_reference,
                'payment_channel' => $payload['payment_channel'] ?? null,
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

            $invoice->visaApplication->update([
                'status' => VisaApplicationStatus::Paid,
                'last_status_changed_at' => now(),
            ]);

            Audit::log(
                action: 'payment.confirmed',
                description: 'Payment confirmed for invoice.',
                auditable: $payment,
                metadata: [
                    'invoice_no' => $invoice->invoice_no,
                    'payment_id' => $payment->id,
                ]
            );

            return $payment;
        });
    }
}
