<?php

// FILE: app/Http/Controllers/VerifyPermitController.php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Services\Verification\VerifyPermitService;
use Carbon\Carbon;
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
            'publicStatus' => $this->resolvePublicStatus($searchedPermit),
        ]);
    }

    protected function resolvePublicStatus(Permit $permit): string
    {
        $status = strtolower($permit->status->value);

        if ($status === 'revoked') {
            return 'Revoked';
        }

        if ($this->isExpired($permit)) {
            return 'Expired';
        }

        return 'Valid';
    }

    protected function isExpired(Permit $permit): bool
    {
        if (! $permit->valid_until) {
            return false;
        }

        try {
            return Carbon::parse($permit->valid_until)->endOfDay()->isPast();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
