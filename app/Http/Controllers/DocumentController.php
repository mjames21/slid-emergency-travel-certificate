<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Permit;
use App\Models\Receipt;
use App\Services\Documents\GenerateInvoicePdfService;
use App\Services\Documents\GeneratePermitPdfService;
use App\Services\Documents\GenerateReceiptPdfService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function invoice(Invoice $invoice, GenerateInvoicePdfService $service): Response
    {
        $this->authorize('view', $invoice);

        $path = $service->handle($invoice);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$invoice->invoice_no.'.pdf"',
        ]);
    }

    public function permit(Permit $permit, GeneratePermitPdfService $service): Response
    {
        $this->authorize('print', $permit);

        $path = $service->handle($permit);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$permit->permit_no.'.pdf"',
        ]);
    }

    public function receipt(Receipt $receipt, GenerateReceiptPdfService $service): Response
    {
        $this->authorize('view', $receipt);

        $path = $service->handle($receipt);

        return response(Storage::disk('local')->get($path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$receipt->receipt_no.'.pdf"',
        ]);
    }
}
