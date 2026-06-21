<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OwaspSecurityCheckCommandTest extends TestCase
{
    #[Test]
    public function owasp_check_passes_with_production_safe_configuration(): void
    {
        $this->setProductionSafeConfig();

        $this->artisan('security:owasp-check', ['--production' => true])
            ->assertExitCode(0);
    }

    #[Test]
    public function owasp_check_fails_when_production_debug_is_enabled(): void
    {
        $this->setProductionSafeConfig([
            'app.debug' => true,
        ]);

        $this->artisan('security:owasp-check', ['--production' => true])
            ->assertExitCode(1);
    }

    #[Test]
    public function owasp_check_fails_when_production_webhook_secret_is_weak(): void
    {
        $this->setProductionSafeConfig([
            'services.wangov.webhook.vendor_secret' => 'short-secret',
        ]);

        $this->artisan('security:owasp-check', ['--production' => true])
            ->assertExitCode(1);
    }

    private function setProductionSafeConfig(array $overrides = []): void
    {
        config(array_replace([
            'app.debug' => false,
            'app.key' => 'base64:test-owasp-key',
            'fortify.limiters.login' => 'login',
            'fortify.limiters.two-factor' => 'two-factor',
            'fortify.limiters.passkeys' => 'passkeys',
            'logging.default' => 'stack',
            'security.headers.enabled' => true,
            'security.headers.content_security_policy' => true,
            'security.headers.hide_powered_by' => true,
            'security.secrets.minimum_shared_secret_length' => 32,
            'security.session_integrity.enabled' => true,
            'security.staff_mfa.required' => true,
            'security.staff_mfa.require_confirmed' => true,
            'services.tesseract.binary' => 'tesseract',
            'services.wangov.enabled' => true,
            'services.wangov.checkout_allowed_hosts' => ['checkout.govpay.sl'],
            'services.wangov.external.base_url' => 'https://wangov.example.test',
            'services.wangov.external.service_key' => 'test-outbound-service-key-1234567890',
            'services.wangov.webhook.vendor_secret' => 'test-webhook-secret-1234567890abcdef',
            'services.wangov.webhook.max_payload_bytes' => 20000,
            'session.encrypt' => true,
            'session.http_only' => true,
            'session.same_site' => 'strict',
            'session.secure' => true,
        ], $overrides));
    }
}
