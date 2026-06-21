<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use App\Models\Permit;
use App\Models\Receipt;
use App\Models\VisaApplication;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAirportAccess
{
    public function handle(Request $request, Closure $next, string $parameter): Response
    {
        $user = $request->user();

        abort_unless($user, 401);

        if ($user->hasAnyStaffTitle([
            'system_administrator',
            'etc_issuer',
            'hq_administrator',
            'compliance_auditor',
            'executive_observer',
        ])) {
            return $next($request);
        }

        $resource = $request->route($parameter);

        if ($resource instanceof VisaApplication) {
            abort_unless($resource->airport_id === $user->primary_airport_id, 403);
        }

        if ($resource instanceof Invoice) {
            abort_unless($resource->visaApplication->airport_id === $user->primary_airport_id, 403);
        }

        if ($resource instanceof Permit) {
            abort_unless($resource->visaApplication->airport_id === $user->primary_airport_id, 403);
        }

        if ($resource instanceof Receipt) {
            abort_unless($resource->payment->invoice->visaApplication->airport_id === $user->primary_airport_id, 403);
        }

        return $next($request);
    }
}
