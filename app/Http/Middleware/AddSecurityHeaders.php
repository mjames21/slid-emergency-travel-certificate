<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! config('security.headers.enabled', true)) {
            return $response;
        }

        $headers = $response->headers;

        if (config('security.headers.hide_powered_by', true)) {
            header_remove('X-Powered-By');
            $headers->remove('X-Powered-By');
        }

        $headers->set('X-Content-Type-Options', 'nosniff', false);
        $headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $headers->set('X-Download-Options', 'noopen', false);
        $headers->set('Referrer-Policy', 'same-origin', false);
        $headers->set('X-Permitted-Cross-Domain-Policies', 'none', false);
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin', false);
        $headers->set('Cross-Origin-Resource-Policy', 'same-origin', false);
        $headers->set('Origin-Agent-Cluster', '?1', false);
        $headers->set(
            'Permissions-Policy',
            'camera=(self), microphone=(), geolocation=(), payment=(), usb=(), fullscreen=(self)',
            false
        );

        if (config('security.headers.content_security_policy', true)) {
            $headers->set('Content-Security-Policy', $this->contentSecurityPolicy(), false);
        }

        if ($request->isSecure() && config('security.headers.hsts', false)) {
            $value = 'max-age=' . (int) config('security.headers.hsts_max_age', 31536000) . '; includeSubDomains';

            if (config('security.headers.hsts_preload', false)) {
                $value .= '; preload';
            }

            $headers->set(
                'Strict-Transport-Security',
                $value,
                false
            );
        }

        if ($this->requiresPrivateCaching($request)) {
            $headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $headers->set('Pragma', 'no-cache');
            $headers->set('Expires', '0');
        }

        return $response;
    }

    private function requiresPrivateCaching(Request $request): bool
    {
        return $request->user() !== null
            || $request->routeIs('staff.*')
            || $request->routeIs('hq.*')
            || $request->routeIs('admin.*')
            || $request->routeIs('dashboard')
            || $request->routeIs('documents.*')
            || $request->routeIs('etc.status')
            || $request->routeIs('verify.permit');
    }

    private function contentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "script-src 'self' 'unsafe-inline' https://cdn.wan.gov.sl https://*.wan.gov.sl https://*.govpay.sl",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src 'self' https://cdn.wan.gov.sl https://*.wan.gov.sl https://*.govpay.sl",
            "media-src 'self' blob:",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
            "frame-src 'self' https://*.wan.gov.sl https://*.govpay.sl",
        ];

        if (config('security.headers.upgrade_insecure_requests', false)) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
