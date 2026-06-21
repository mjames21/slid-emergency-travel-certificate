<?php

namespace App\Services\Billing;

use App\Enums\InvoiceStatus;
use App\Enums\VisaApplicationStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Models\VisaApplication;
use App\Support\Audit;
use App\Support\InvoiceNumberGenerator;
use App\Support\PaymentReferenceGenerator;
use Illuminate\Support\Facades\DB;

class GenerateInvoiceService
{
    public function __construct(
        protected InvoiceNumberGenerator $invoiceNumberGenerator,
        protected PaymentReferenceGenerator $paymentReferenceGenerator
    ) {
    }

    public function handle(User $user, VisaApplication $application, float $amount, string $currency): Invoice
    {
        return DB::transaction(function () use ($user, $application, $amount, $currency) {
            $existing = $application->invoices()
                ->whereIn('status', ['pending', 'initiated', 'paid', 'waived'])
                ->latest()
                ->first();

            if ($existing) {
                return $existing;
            }

            $invoice = Invoice::query()->create([
                'invoice_no' => $this->invoiceNumberGenerator->generate($application->airport),
                'visa_application_id' => $application->id,
                'created_by' => $user->id,
                'amount' => $amount,
                'currency' => $currency,
                'payment_reference' => $this->paymentReferenceGenerator->generate($application->airport),
                'gateway' => 'wangov',
                'status' => $amount > 0 ? InvoiceStatus::Pending : InvoiceStatus::Waived,
                'issued_at' => now(),
                'expires_at' => now()->addDay(),
                'paid_at' => $amount == 0.0 ? now() : null,
            ]);

            $application->update([
                'status' => $amount > 0
                    ? VisaApplicationStatus::AwaitingPayment
                    : VisaApplicationStatus::UnderReview,
                'last_status_changed_at' => now(),
            ]);

            Audit::log(
                action: 'invoice.created',
                description: 'Invoice created for visa application.',
                auditable: $invoice,
                metadata: [
                    'invoice_no' => $invoice->invoice_no,
                    'payment_reference' => $invoice->payment_reference,
                ]
            );

            return $invoice;
        });
    }
}