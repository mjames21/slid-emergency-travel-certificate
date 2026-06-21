<?php

namespace App\Services\Reporting;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\Receipt;
use App\Models\VisaApplication;
use Illuminate\Support\Collection;

class ReconciliationService
{
    public function summary(): array
    {
        return [
            'applications_without_invoice' => VisaApplication::query()->doesntHave('invoices')->count(),
            'paid_invoices_without_payment' => Invoice::query()->where('status', 'paid')->doesntHave('payments')->count(),
            'payments_without_receipt' => Payment::query()->where('status', 'successful')->doesntHave('receipt')->count(),
            'permits_without_payment_or_waiver' => Permit::query()
                ->whereNull('payment_id')
                ->whereNull('waiver_approval_id')
                ->count(),
            'receipts_without_document_hash' => Receipt::query()->whereNull('document_hash')->count(),
        ];
    }

    public function mismatches(): Collection
    {
        return collect([
            'applications_without_invoice' => VisaApplication::query()
                ->with(['passenger', 'airport'])
                ->doesntHave('invoices')
                ->latest()
                ->limit(25)
                ->get(),
            'paid_invoices_without_payment' => Invoice::query()
                ->with(['visaApplication.passenger', 'visaApplication.airport'])
                ->where('status', 'paid')
                ->doesntHave('payments')
                ->latest()
                ->limit(25)
                ->get(),
            'payments_without_receipt' => Payment::query()
                ->with(['invoice.visaApplication.passenger', 'invoice.visaApplication.airport'])
                ->where('status', 'successful')
                ->doesntHave('receipt')
                ->latest()
                ->limit(25)
                ->get(),
            'permits_without_payment_or_waiver' => Permit::query()
                ->with(['visaApplication.passenger', 'visaApplication.airport'])
                ->whereNull('payment_id')
                ->whereNull('waiver_approval_id')
                ->latest()
                ->limit(25)
                ->get(),
        ]);
    }
}
