<?php

namespace App\Services\Documents;

use App\Models\Receipt;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GenerateReceiptPdfService
{
    public function handle(Receipt $receipt): string
    {
        $receipt->loadMissing([
            'payment.invoice.visaApplication.passenger',
            'payment.invoice.visaApplication.airport',
            'issuer',
        ]);

        $path = 'documents/receipts/' . $receipt->receipt_no . '.pdf';

        if (Storage::disk('local')->exists($path)) {
            return $path;
        }

        $pdf = Pdf::loadView('pdf.receipt', [
            'receipt' => $receipt,
        ])->setPaper('a4');

        Storage::disk('local')->put($path, $pdf->output());

        $receipt->update([
            'document_path' => $path,
            'printed_at' => $receipt->printed_at ?: now(),
        ]);

        return $path;
    }
}
