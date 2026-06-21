<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIntegrity
{
    private const SESSION_KEY = 'security.session_fingerprint';

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('security.session_integrity.enabled', true) || ! $request->hasSession() || ! $request->user()) {
            return $next($request);
        }

        $session = $request->session();
        $fingerprint = $this->fingerprint($request);
        $storedFingerprint = $session->get(self::SESSION_KEY);

        if ($storedFingerprint === null) {
            $session->put(self::SESSION_KEY, $fingerprint);

            return $next($request);
        }

        if (hash_equals((string) $storedFingerprint, $fingerprint)) {
            return $next($request);
        }

        Log::warning('Authenticated session integrity check failed.', [
            'user_id' => $request->user()->getAuthIdentifier(),
            'ip_prefix' => $this->ipPrefix((string) $request->ip()),
            'user_agent_hash' => hash('sha256', (string) $request->userAgent()),
        ]);

        Auth::guard()->logout();
        $session->invalidate();
        $session->regenerateToken();

        if ($request->expectsJson()) {
            abort(401, 'Session security check failed.');
        }

        return redirect()
            ->route('login')
            ->withErrors([
                'email' => 'Your session was ended because the browser or network changed. Please sign in again.',
            ]);
    }

    private function fingerprint(Request $request): string
    {
        $parts = [
            'user_id' => (string) $request->user()->getAuthIdentifier(),
        ];

        if (config('security.session_integrity.bind_user_agent', true)) {
            $parts['user_agent'] = hash('sha256', (string) $request->userAgent());
        }

        if (config('security.session_integrity.bind_ip_prefix', true)) {
            $parts['ip_prefix'] = $this->ipPrefix((string) $request->ip());
        }

        return hash_hmac('sha256', json_encode($parts, JSON_THROW_ON_ERROR), (string) config('app.key', ''));
    }

    private function ipPrefix(string $ip): string
    {
        $packed = @inet_pton($ip);

        if ($packed === false) {
            return 'unknown';
        }

        $isIpv6 = str_contains($ip, ':');
        $prefixBits = (int) ($isIpv6
            ? config('security.session_integrity.ipv6_prefix_bits', 64)
            : config('security.session_integrity.ipv4_prefix_bits', 24));

        $prefixBits = max(4, min($isIpv6 ? 128 : 32, $prefixBits));
        $hexCharacters = (int) ceil($prefixBits / 4);

        return ($isIpv6 ? 'v6:' : 'v4:') . substr(bin2hex($packed), 0, $hexCharacters);
    }
}
