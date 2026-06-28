<?php

// FILE: app/Services/Documents/GeneratePermitPdfService.php

namespace App\Services\Documents;

use App\Models\Permit;
use App\Models\User;
use App\Support\Audit;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class GeneratePermitPdfService
{
    public function __construct(
        protected GenerateQrCodeService $generateQrCodeService
    ) {}

    public function handle(
        Permit $permit,
        ?User $printedBy = null,
        bool $isReprint = false,
        ?string $reasonCode = null,
        ?string $reason = null
    ): string {
        $permit->loadMissing([
            'visaApplication.passenger',
            'payment',
            'issuer',
        ]);

        $path = 'documents/permits/'.$permit->permit_no.'.pdf';

        if (! $permit->qr_code_path || ! Storage::disk('local')->exists($permit->qr_code_path)) {
            $permit->update([
                'qr_code_path' => $this->generateQrCodeService->handle($permit),
            ]);

            $permit->refresh();
        }

        $qrRaw = Storage::disk('local')->get($permit->qr_code_path);
        $qrImageBase64 = 'data:image/svg+xml;base64,'.base64_encode($qrRaw);

        $pdf = Pdf::loadView('pdf.permit', [
            'permit' => $permit->fresh([
                'visaApplication.passenger',
                'payment',
                'issuer',
            ]),
            'verificationUrl' => route('verify.permit', $permit->verification_code),
            'qrImageBase64' => $qrImageBase64,
        ])->setPaper('a4');

        Storage::disk('local')->put($path, $pdf->output());

        $permit->update([
            'document_path' => $path,
            'print_count' => $permit->print_count + 1,
            'last_printed_at' => now(),
            'is_duplicate_print' => $isReprint || $permit->print_count > 0,
        ]);

        if ($printedBy) {
            $permit->printLogs()->create([
                'printed_by' => $printedBy->id,
                'terminal_name' => request()->header('X-Terminal-Name'),
                'printer_name' => request()->header('X-Printer-Name'),
                'is_reprint' => $isReprint,
                'reason_code' => $reasonCode,
                'reason' => $reason,
                'printed_at' => now(),
            ]);
        }

        Audit::log(
            action: $isReprint ? 'permit.reprinted' : 'permit.printed',
            description: $isReprint ? 'Emergency Travel Certificate reprinted.' : 'Emergency Travel Certificate printed.',
            auditable: $permit,
            metadata: [
                'permit_no' => $permit->permit_no,
                'print_count' => $permit->fresh()->print_count,
                'reason_code' => $reasonCode,
            ]
        );

        return $path;
    }
}
