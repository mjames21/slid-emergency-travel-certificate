<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureValidSessionCookieDomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredDomain = config('session.domain');

        if (! is_string($configuredDomain) || trim($configuredDomain) === '') {
            return $next($request);
        }

        if ($this->domainAllowsHost($configuredDomain, $request->getHost())) {
            return $next($request);
        }

        Log::warning('Session cookie domain does not match the request host; using a host-only cookie.', [
            'configured_domain' => $configuredDomain,
            'request_host' => $request->getHost(),
        ]);

        config(['session.domain' => null]);

        try {
            return $next($request);
        } finally {
            config(['session.domain' => $configuredDomain]);
        }
    }

    private function domainAllowsHost(string $configuredDomain, string $host): bool
    {
        $domain = strtolower(ltrim(trim($configuredDomain), '.'));
        $host = strtolower(trim($host, '.'));

        return $host === $domain || str_ends_with($host, '.'.$domain);
    }
}
