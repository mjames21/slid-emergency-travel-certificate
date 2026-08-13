<?php

namespace App\Http\Controllers;

use App\Enums\PermitVerificationResult;
use App\Models\Permit;
use App\Services\Documents\GenerateQrCodeService;
use App\Services\Verification\VerifyPermitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DigitalCertificateController extends Controller
{
    public function __invoke(
        Request $request,
        string $code,
        VerifyPermitService $verifyPermitService,
        GenerateQrCodeService $generateQrCodeService
    ): View {
        $result = $verifyPermitService->handle($code, [
            'channel' => 'digital_certificate',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $permit = $result['permit'];

        abort_unless($permit instanceof Permit, 404);
        abort_unless($permit->is_virtual_available, 404);

        $permit->loadMissing([
            'visaApplication.passenger',
            'payment',
            'issuer',
        ]);

        if (! $permit->qr_code_path || ! Storage::disk('local')->exists($permit->qr_code_path)) {
            $permit->update([
                'qr_code_path' => $generateQrCodeService->handle($permit),
            ]);

            $permit->refresh();
        }

        $qrRaw = Storage::disk('local')->get($permit->qr_code_path);

        return view('digital.permit', [
            'permit' => $permit,
            'publicStatus' => $this->statusLabel($result['result']),
            'verificationUrl' => route('verify.permit', $permit->verification_code),
            'qrImageBase64' => 'data:image/svg+xml;base64,'.base64_encode($qrRaw),
        ]);
    }

    private function statusLabel(PermitVerificationResult $result): string
    {
        return match ($result) {
            PermitVerificationResult::Cancelled => 'Cancelled',
            PermitVerificationResult::Revoked => 'Revoked',
            PermitVerificationResult::Expired => 'Expired',
            PermitVerificationResult::Valid => 'Valid',
            PermitVerificationResult::NotFound => 'Not Found',
        };
    }
}
