<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\EnsureSessionIntegrity;
use App\Http\Middleware\EnsureStaffAccess;
use App\Http\Middleware\EnsureStaffMfaEnabled;
use App\Http\Middleware\EnsureStaffTitle;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureValidSessionCookieDomain;
use App\Http\Middleware\EnsureVerifiedPermitAccess;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->append([
            AddSecurityHeaders::class,
        ]);

        $middleware->web(
            prepend: [EnsureValidSessionCookieDomain::class],
            append: [EnsureSessionIntegrity::class],
        );

        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'staff.access' => EnsureStaffAccess::class,
            'staff.mfa' => EnsureStaffMfaEnabled::class,
            'staff.title' => EnsureStaffTitle::class,
            'verified.permit.access' => EnsureVerifiedPermitAccess::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'webhooks/wangov',
            'api/wangov/payment-update',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {})
    ->create();
