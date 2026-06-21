<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVerifiedPermitAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->route('code') || $request->route('permit'), 404);

        return $next($request);
    }
}
