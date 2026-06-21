<?php

// FILE: app/Http/Controllers/VerifyPermitController.php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Services\Verification\VerifyPermitService;
use App\Support\PermitLifecycleStatus;
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

        [$displayPermit, $notices] = $this->resolveDisplayPermit($searchedPermit);

        return view('verify.permit', [
            'searchedPermit' => $searchedPermit,
            'permit' => $displayPermit,
            'notices' => $notices,
            'publicStatus' => $this->resolvePublicStatus($displayPermit),
        ]);
    }

    protected function resolveDisplayPermit(Permit $permit): array
    {
        $current = $permit;
        $notices = [];
        $visited = [];

        for ($i = 0; $i < 10; $i++) {
            if (in_array($current->id, $visited, true)) {
                break;
            }

            $visited[] = $current->id;

            $status = strtolower(PermitLifecycleStatus::value($current));

            if (! in_array($status, ['extended', 'replaced'], true)) {
                break;
            }

            $nextPermit = Permit::query()
                ->with([
                    'visaApplication.passenger',
                    'issuer',
                ])
                ->where('parent_permit_id', $current->id)
                ->latest('id')
                ->first();

            if (! $nextPermit) {
                break;
            }

            $notices[] = $status === 'extended'
                ? sprintf('Permit %s was extended and replaced by permit %s.', $current->permit_no, $nextPermit->permit_no)
                : sprintf('Permit %s was replaced by permit %s.', $current->permit_no, $nextPermit->permit_no);

            $current = $nextPermit;
        }

        return [$current, $notices];
    }

    protected function resolvePublicStatus(Permit $permit): string
    {
        $status = strtolower(PermitLifecycleStatus::value($permit));

        if ($status === 'revoked') {
            return 'Revoked';
        }

        if ($status === 'replaced') {
            return 'Replaced';
        }

        if ($status === 'extended') {
            return 'Extended';
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
