<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStaffTitle
{
    public function handle(Request $request, Closure $next, string ...$titles): Response
    {
        $user = $request->user();

        abort_unless($user, 401);
        abort_unless($user->hasAnyStaffTitle($titles), 403);

        return $next($request);
    }
}