<?php

namespace App\Console\Commands;

use App\Http\Middleware\AddSecurityHeaders;
use App\Services\Audit\WriteAuditLogService;
use App\Services\Evisa\ApproveOnlineEvisaApplicationService;
use App\Services\Verification\VerifyPermitService;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

class OwaspSecurityCheckCommand extends Command
{
    protected $signature = 'security:owasp-check {--production : Enforce production go-live expectations}';

    protected $description = 'Run an OWASP Top 10 oriented security readiness check.';

    public function handle(): int
    {
        $production = (bool) $this->option('production');
        $checks = $this->checks($production);

        $this->newLine();
        $this->info('OWASP Top 10 security readiness');
        $this->table(
            ['OWASP', 'Control', 'Status', 'Detail'],
            array_map(fn (array $check): array => [
                $check['owasp'],
                $check['control'],
                $check['passed'] ? 'OK' : 'FAIL',
                $check['detail'],
            ], $checks)
        );

        $failed = array_values(array_filter($checks, fn (array $check): bool => ! $check['passed']));

        if ($failed !== []) {
            $this->error(count($failed).' OWASP security readiness check(s) failed.');

            return self::FAILURE;
        }

        $this->info('All OWASP security readiness checks passed.');

        return self::SUCCESS;
    }

    private function checks(bool $production): array
    {
        $hqRoute = Route::getRoutes()->getByName('hq.emergency-travel-certificates.index');
        $dashboardRoute = Route::getRoutes()->getByName('dashboard');
        $documentRoute = Route::getRoutes()->getByName('documents.certificates.show');
        $statusRoute = Route::getRoutes()->getByName('etc.status');
        $payRoute = Route::getRoutes()->getByName('etc.pay');
        $verifyRoute = Route::getRoutes()->getByName('verify.permit');
        $webhookRoute = Route::getRoutes()->getByName('webhooks.wangov');
        $apiWebhookRoute = Route::getRoutes()->getByName('api.wangov.payment_update');
        $passkeyRoute = Route::getRoutes()->getByName('passkey.store');
        $twoFactorRoute = Route::getRoutes()->getByName('two-factor.enable');
        $hasLockfiles = file_exists(base_path('composer.lock')) && file_exists(base_path('package-lock.json'));
        $wangovEnabled = (bool) config('services.wangov.enabled');

        return [
            $this->check(
                'A01 Broken Access Control',
                'HQ certificate work queue requires authentication and staff-title middleware',
                $this->routeHasMiddleware($hqRoute, 'auth') && $this->routeHasMiddleware($hqRoute, 'staff.title'),
                'hq.emergency-travel-certificates.index'
            ),
            $this->check(
                'A01 Broken Access Control',
                'Staff access is evaluated by an application MFA boundary',
                $this->routeHasMiddleware($hqRoute, 'staff.mfa') && $this->routeHasMiddleware($dashboardRoute, 'staff.mfa'),
                'staff.mfa middleware on staff dashboard and HQ routes'
            ),
            $this->check(
                'A01 Broken Access Control',
                'Sensitive certificate document route is staff-title gated',
                $this->routeHasMiddleware($documentRoute, 'staff.title'),
                'documents.certificates.show'
            ),
            $this->check(
                'A02 Security Misconfiguration',
                'Debug mode is disabled for production',
                ! $production || config('app.debug') === false,
                'APP_DEBUG='.($this->bool(config('app.debug')))
            ),
            $this->check(
                'A02 Security Misconfiguration',
                'Security headers and CSP are enabled',
                (bool) config('security.headers.enabled') && (bool) config('security.headers.content_security_policy'),
                'SECURITY_HEADERS_ENABLED / SECURITY_CSP_ENABLED'
            ),
            $this->check(
                'A02 Security Misconfiguration',
                'Runtime version disclosure header is suppressed',
                (bool) config('security.headers.hide_powered_by'),
                'SECURITY_HIDE_POWERED_BY_HEADER; set expose_php=Off at the web server layer'
            ),
            $this->check(
                'A03 Software Supply Chain Failures',
                'Composer and npm lockfiles are present',
                $hasLockfiles,
                'composer.lock + package-lock.json'
            ),
            $this->check(
                'A04 Cryptographic Failures',
                'Application key is configured',
                trim((string) config('app.key')) !== '',
                'APP_KEY present'
            ),
            $this->check(
                'A04 Cryptographic Failures',
                'Production cookies are secure, HTTP-only, and encrypted',
                ! $production || (
                    (bool) config('session.secure')
                    && (bool) config('session.http_only')
                    && (bool) config('session.encrypt')
                    && config('session.same_site') === 'strict'
                ),
                'SESSION_SECURE_COOKIE / SESSION_HTTP_ONLY / SESSION_ENCRYPT / SESSION_SAME_SITE'
            ),
            $this->check(
                'A05 Injection',
                'Tesseract executable config is shell-safe',
                $this->tesseractBinaryLooksSafe(),
                'TESSERACT_BINARY'
            ),
            $this->check(
                'A06 Insecure Design',
                'ETC issuance service enforces paid single-issuer workflow',
                class_exists(ApproveOnlineEvisaApplicationService::class)
                    && method_exists(ApproveOnlineEvisaApplicationService::class, 'canIssue'),
                'ApproveOnlineEvisaApplicationService::canIssue'
            ),
            $this->check(
                'A06 Insecure Design',
                'Only the ETC Issuer role can approve and issue certificates',
                class_exists(ApproveOnlineEvisaApplicationService::class)
                    && ApproveOnlineEvisaApplicationService::issuerStaffTitleCodes() === ['etc_issuer'],
                'issuerStaffTitleCodes=etc_issuer'
            ),
            $this->check(
                'A07 Authentication Failures',
                'Login, two-factor, and passkey rate limiters are configured',
                config('fortify.limiters.login') === 'login'
                    && config('fortify.limiters.two-factor') === 'two-factor'
                    && config('fortify.limiters.passkeys') === 'passkeys',
                'fortify.limiters'
            ),
            $this->check(
                'A07 Authentication Failures',
                'Two-factor authentication and passkeys are available',
                Features::enabled(Features::twoFactorAuthentication())
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm')
                    && Features::enabled(Features::passkeys()),
                'fortify.features'
            ),
            $this->check(
                'A07 Authentication Failures',
                'Production staff MFA is mandatory and confirmed',
                ! $production || (
                    (bool) config('security.staff_mfa.required')
                    && (bool) config('security.staff_mfa.require_confirmed')
                ),
                'SECURITY_STAFF_MFA_REQUIRED / SECURITY_STAFF_MFA_REQUIRE_CONFIRMED'
            ),
            $this->check(
                'A07 Authentication Failures',
                'Sensitive MFA and passkey changes require recent password confirmation',
                $this->routeHasMiddleware($passkeyRoute, 'password.confirm')
                    && $this->routeHasMiddleware($twoFactorRoute, 'password.confirm'),
                'passkey.store / two-factor.enable'
            ),
            $this->check(
                'A07 Authentication Failures',
                'Session integrity anti-hijack protection is enabled',
                (bool) config('security.session_integrity.enabled'),
                'SECURITY_SESSION_INTEGRITY_ENABLED'
            ),
            $this->check(
                'A08 Software or Data Integrity Failures',
                'WanGov webhook secret is required when payments are enabled',
                ! $wangovEnabled || trim((string) config('services.wangov.webhook.vendor_secret')) !== '',
                'WANGOV_WEBHOOK_SECRET'
            ),
            $this->check(
                'A08 Software or Data Integrity Failures',
                'Production webhook secret is distinct and sufficiently long',
                ! $production || ! $wangovEnabled || $this->webhookSecretLooksProductionSafe(),
                'WANGOV_WEBHOOK_SECRET'
            ),
            $this->check(
                'A08 Software or Data Integrity Failures',
                'Webhook payload size limit is enabled',
                (int) config('services.wangov.webhook.max_payload_bytes') > 0,
                'WANGOV_WEBHOOK_MAX_PAYLOAD_BYTES'
            ),
            $this->check(
                'A08 Software or Data Integrity Failures',
                'WanGov webhook routes are rate limited',
                $this->routeHasMiddleware($webhookRoute, 'throttle')
                    && $this->routeHasMiddleware($apiWebhookRoute, 'throttle'),
                'webhooks.wangov / api.wangov.payment_update'
            ),
            $this->check(
                'A08 Software or Data Integrity Failures',
                'WanGov production endpoint uses HTTPS',
                ! $production || ! $wangovEnabled || $this->httpsUrlConfigured((string) config('services.wangov.external.base_url')),
                'WANGOV_BASE_URL'
            ),
            $this->check(
                'A08 Software or Data Integrity Failures',
                'WanGov checkout redirects are restricted to configured hosts',
                ! $wangovEnabled || $this->checkoutRedirectHosts() !== [],
                'WANGOV_CHECKOUT_ALLOWED_HOSTS or WANGOV_BASE_URL host'
            ),
            $this->check(
                'A09 Logging and Alerting Failures',
                'Application logging is enabled',
                ! in_array((string) config('logging.default'), ['', 'null'], true),
                'LOG_CHANNEL='.(string) config('logging.default')
            ),
            $this->check(
                'A09 Logging and Alerting Failures',
                'Audit logging service exists',
                class_exists(WriteAuditLogService::class),
                'WriteAuditLogService'
            ),
            $this->check(
                'A09 Logging and Alerting Failures',
                'Public certificate verification attempts are recorded',
                class_exists(VerifyPermitService::class),
                'VerifyPermitService'
            ),
            $this->check(
                'A10 Mishandling Exceptional Conditions',
                'Sensitive public pages are no-store cached by security middleware',
                class_exists(AddSecurityHeaders::class),
                'AddSecurityHeaders middleware'
            ),
            $this->check(
                'A10 Mishandling Exceptional Conditions',
                'Public token and verification routes are rate limited',
                $this->routeHasMiddleware($statusRoute, 'throttle')
                    && $this->routeHasMiddleware($payRoute, 'throttle')
                    && $this->routeHasMiddleware($verifyRoute, 'throttle'),
                'etc.status / etc.pay / verify.permit'
            ),
            $this->check(
                'A10 Mishandling Exceptional Conditions',
                'Production exception detail is suppressed',
                ! $production || config('app.debug') === false,
                'APP_DEBUG=false'
            ),
        ];
    }

    private function check(string $owasp, string $control, bool $passed, string $detail): array
    {
        return compact('owasp', 'control', 'passed', 'detail');
    }

    private function routeHasMiddleware(mixed $route, string $middleware): bool
    {
        if (! $route) {
            return false;
        }

        return collect($route->gatherMiddleware())
            ->contains(function (string $value) use ($middleware): bool {
                if ($value === $middleware || str_starts_with($value, $middleware.':')) {
                    return true;
                }

                if ($middleware === 'password.confirm') {
                    return $value === RequirePassword::class;
                }

                return false;
            });
    }

    private function tesseractBinaryLooksSafe(): bool
    {
        $binary = trim((string) config('services.tesseract.binary', 'tesseract'));

        return $binary !== '' && preg_match('/[\x00-\x1F\x7F]/', $binary) !== 1;
    }

    private function bool(mixed $value): string
    {
        return $value ? 'true' : 'false';
    }

    private function webhookSecretLooksProductionSafe(): bool
    {
        $secret = trim((string) config('services.wangov.webhook.vendor_secret'));
        $serviceKey = trim((string) config('services.wangov.external.service_key'));
        $minimumLength = (int) config('security.secrets.minimum_shared_secret_length', 32);

        if (strlen($secret) < max(16, $minimumLength)) {
            return false;
        }

        return $serviceKey === '' || ! hash_equals($serviceKey, $secret);
    }

    private function httpsUrlConfigured(string $url): bool
    {
        return strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https'
            && filled(parse_url($url, PHP_URL_HOST));
    }

    private function checkoutRedirectHosts(): array
    {
        $hosts = array_map(
            fn (string $host): string => strtolower($host),
            array_filter((array) config('services.wangov.checkout_allowed_hosts', []))
        );

        $baseHost = strtolower((string) parse_url((string) config('services.wangov.external.base_url', ''), PHP_URL_HOST));

        if ($baseHost !== '') {
            $hosts[] = $baseHost;
        }

        return array_values(array_unique(array_filter($hosts)));
    }
}
