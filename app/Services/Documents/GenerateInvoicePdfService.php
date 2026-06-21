<?php

namespace App\Services\Documents;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateInvoicePdfService
{
    public function handle(Invoice $invoice): string
    {
        $invoice->loadMissing([
            'visaApplication.passenger',
            'visaApplication.airport',
            'visaApplication.desk',
            'creator',
        ]);

        $path = 'documents/invoices/' . $invoice->invoice_no . '.pdf';

        if (Storage::disk('local')->exists($path)) {
            return $path;
        }

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }
}
