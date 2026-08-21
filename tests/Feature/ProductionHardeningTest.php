<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Permit;
use App\Models\PermitVerification;
use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function public_pages_emit_baseline_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Download-Options', 'noopen')
            ->assertHeader('Referrer-Policy', 'same-origin')
            ->assertHeader('X-Permitted-Cross-Domain-Policies', 'none')
            ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->assertHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->assertHeader('Origin-Agent-Cluster', '?1');

        $this->assertStringContainsString('camera=(self)', $response->headers->get('Permissions-Policy'));

        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $contentSecurityPolicy);
        $this->assertStringContainsString("object-src 'none'", $contentSecurityPolicy);
        $this->assertStringContainsString("frame-ancestors 'self'", $contentSecurityPolicy);
        $this->assertStringContainsString("base-uri 'self'", $contentSecurityPolicy);
        $this->assertStringContainsString('https://cdn.wan.gov.sl', $contentSecurityPolicy);
        $this->assertStringNotContainsString("'unsafe-eval'", $contentSecurityPolicy);
    }

    #[Test]
    public function mismatched_session_cookie_domain_falls_back_to_a_host_only_cookie(): void
    {
        config([
            'session.domain' => '.slid.gov.sl',
            'session.secure' => true,
        ]);

        $response = $this->get('https://etc.slid.datahub.gov.sl/login');
        $sessionCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie): bool => $cookie->getName() === config('session.cookie'));

        $response->assertOk();
        $this->assertNotNull($sessionCookie);
        $this->assertNull($sessionCookie->getDomain());
    }

    #[Test]
    public function livewire_staff_pages_allow_the_runtime_evaluator_without_relaxing_public_pages(): void
    {
        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        $response = $this->get('/hq/emergency-travel-certificates');

        $response->assertOk();

        $contentSecurityPolicy = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self' 'unsafe-inline'", $contentSecurityPolicy);
        $this->assertStringContainsString("'unsafe-eval'", $contentSecurityPolicy);
    }

    #[Test]
    public function livewire_asset_responses_allow_the_runtime_evaluator(): void
    {
        $response = $this->get('/livewire/livewire.js');

        $response->assertOk();

        $this->assertStringContainsString(
            "'unsafe-eval'",
            (string) $response->headers->get('Content-Security-Policy')
        );
    }

    #[Test]
    public function secure_production_requests_emit_hsts_when_enabled(): void
    {
        config([
            'security.headers.hsts' => true,
            'security.headers.hsts_preload' => true,
            'security.headers.hsts_max_age' => 31536000,
        ]);

        $response = $this->get('https://localhost/login');

        $response->assertOk();

        $this->assertSame(
            'max-age=31536000; includeSubDomains; preload',
            $response->headers->get('Strict-Transport-Security')
        );
    }

    #[Test]
    public function sensitive_office_status_pages_are_not_browser_cached(): void
    {
        Storage::fake('local');

        config(['features.emergency_travel_certificate' => true]);

        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        Country::query()->create([
            'name' => 'Sierra Leone',
            'iso2' => 'SL',
            'iso3' => 'SLE',
            'nationality' => 'Sierra Leonean',
            'active' => true,
            'sort_order' => 1,
        ]);

        $response = $this->post('/emergency-travel-certificate/apply', [
            'applicant_category' => 'adult',
            'regional_category' => 'ecowas',
            'identity_document_type' => 'passport',
            'surname' => 'JAMES',
            'given_names' => 'MOHAMED',
            'nationality' => 'Sierra Leone',
            'nationality_code' => 'SLE',
            'passport_number' => 'SLR092377',
            'passport_biodata_image' => UploadedFile::fake()->image('passport.jpg', 1400, 900),
            'applicant_photo' => UploadedFile::fake()->image('photo.jpg', 600, 600),
            'sex' => 'M',
            'date_of_birth_year' => '1986',
            'date_of_birth_month' => '04',
            'date_of_birth_day' => '21',
            'place_of_birth' => 'Kenema',
            'country_of_birth' => 'Sierra Leone',
            'applicant_address' => '15 Sumaila Town',
            'occupation' => 'Consultant',
            'email' => 'traveler@example.test',
            'phone' => '+232700000000',
            'point_of_entry' => 'Emergency Travel Certificate Desk',
            'purpose_of_visit' => 'Family emergency',
            'destination_country' => 'Guinea',
            'applicant_certification' => '1',
        ]);

        $response->assertRedirect();

        $status = $this->get($response->headers->get('Location'));

        $status->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');

        $cacheControl = (string) $status->headers->get('Cache-Control');

        foreach (['no-store', 'no-cache', 'must-revalidate', 'private'] as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }
    }

    #[Test]
    public function generic_private_storage_routes_are_not_registered_by_default(): void
    {
        $this->assertFalse(
            collect(Route::getRoutes())->contains(fn ($route) => str_starts_with($route->uri(), 'storage/{path}'))
        );
    }

    #[Test]
    public function digital_etc_certificate_pages_are_not_browser_cached(): void
    {
        Storage::fake('local');

        $permit = Permit::factory()->create([
            'verification_code' => 'SVV-NO-CACHE-DIGITAL',
        ]);

        $response = $this->get(route('digital.certificates.show', $permit->verification_code));

        $response->assertOk()
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');

        $cacheControl = (string) $response->headers->get('Cache-Control');

        foreach (['no-store', 'no-cache', 'must-revalidate', 'private'] as $directive) {
            $this->assertStringContainsString($directive, $cacheControl);
        }
    }

    #[Test]
    public function office_etc_status_token_guessing_is_rate_limited_by_ip(): void
    {
        config(['features.emergency_travel_certificate' => true]);

        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
                ->get("/emergency-travel-certificate/status/guessed-token-{$attempt}")
                ->assertNotFound();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.20'])
            ->get('/emergency-travel-certificate/status/guessed-token-final')
            ->assertStatus(429);
    }

    #[Test]
    public function public_permit_verification_code_guessing_is_rate_limited_by_ip(): void
    {
        for ($attempt = 0; $attempt < 30; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.21'])
                ->get("/verify/guessed-code-{$attempt}")
                ->assertNotFound();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.21'])
            ->get('/verify/guessed-code-final')
            ->assertStatus(429);
    }

    #[Test]
    public function public_permit_verification_attempts_are_recorded(): void
    {
        $permit = Permit::factory()->create([
            'verification_code' => 'SVV-TEST-VERIFICATION',
        ]);

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.44',
            'HTTP_USER_AGENT' => 'CertificateVerifier/1.0',
        ])->get('/verify/SVV-TEST-VERIFICATION')
            ->assertOk();

        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.45',
            'HTTP_USER_AGENT' => 'UnknownVerifier/1.0',
        ])->get('/verify/SVV-UNKNOWN-CODE')
            ->assertNotFound();

        $this->assertDatabaseHas('permit_verifications', [
            'permit_id' => $permit->id,
            'verification_code' => 'SVV-TEST-VERIFICATION',
            'result' => 'valid',
            'channel' => 'public_portal',
            'ip_address' => '203.0.113.44',
        ]);

        $this->assertDatabaseHas('permit_verifications', [
            'permit_id' => null,
            'verification_code' => 'SVV-UNKNOWN-CODE',
            'result' => 'not_found',
            'channel' => 'public_portal',
            'ip_address' => '203.0.113.45',
        ]);

        $this->assertSame(2, PermitVerification::query()->count());
    }

    private function actingAsStaffUserWithTitle(string $code, string $name): User
    {
        $title = StaffTitle::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "{$name} test role",
                'active' => true,
            ]
        );

        $user = User::factory()->create(['active' => true]);

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        $this->actingAs($user);

        return $user->fresh(['staffTitles']);
    }
}
