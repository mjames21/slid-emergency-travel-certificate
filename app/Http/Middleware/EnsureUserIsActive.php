<?php

namespace App\Http\Middleware;

use App\Support\StaffEmailDomains;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isActive()) {
            return $this->reject($request, 'Your account is inactive.');
        }

        if (! StaffEmailDomains::allows((string) $user->email)) {
            return $this->reject($request, 'Sign in with an approved staff email address.');
        }

        return $next($request);
    }

    private function reject(Request $request, string $message): Response
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => $message,
            ]);
    }
}
