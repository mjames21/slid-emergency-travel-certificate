<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffMfaEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! (bool) config('security.staff_mfa.required')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if ($this->hasRequiredMfa($user)) {
            return $next($request);
        }

        Log::notice('Staff access blocked until MFA is enabled.', [
            'user_id' => $user->id,
            'route' => optional($request->route())->getName(),
        ]);

        if ($request->expectsJson()) {
            abort(403, 'Multi-factor authentication is required for staff access.');
        }

        $target = Route::has('profile.show') ? route('profile.show') : url('/user/profile');

        return redirect($target)->with(
            'status',
            'Multi-factor authentication is required before accessing staff systems.'
        );
    }

    private function hasRequiredMfa(mixed $user): bool
    {
        if (blank($user->two_factor_secret)) {
            return false;
        }

        if (! (bool) config('security.staff_mfa.require_confirmed')) {
            return true;
        }

        return filled($user->two_factor_confirmed_at);
    }
}
