<?php

namespace App\Http\Controllers;

use App\Enums\PermitVerificationResult;
use App\Models\Permit;
use App\Services\Verification\VerifyPermitService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class VerifyPermitController extends Controller
{
    public function __invoke(Request $request, string $code, VerifyPermitService $service): View
    {
        $result = $service->handle($code, [
            'channel' => 'public_portal',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $searchedPermit = $result['permit'];

        abort_unless($searchedPermit instanceof Permit, 404);

        $searchedPermit->loadMissing([
            'visaApplication.passenger',
            'issuer',
        ]);

        return view('verify.permit', [
            'searchedPermit' => $searchedPermit,
            'permit' => $searchedPermit,
            'notices' => [],
            'publicStatus' => $this->statusLabel($result['result']),
        ]);
    }

    private function statusLabel(PermitVerificationResult $result): string
    {
        return match ($result) {
            PermitVerificationResult::Cancelled => 'Cancelled',
            PermitVerificationResult::Revoked => 'Revoked',
            PermitVerificationResult::Expired => 'Expired',
            PermitVerificationResult::Valid => 'Valid',
            PermitVerificationResult::Invalid => 'Invalid',
            PermitVerificationResult::NotFound => 'Not Found',
        };
    }
}
