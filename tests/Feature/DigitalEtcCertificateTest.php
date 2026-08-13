<?php

namespace Tests\Feature;

use App\Models\Permit;
use App\Models\PermitVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DigitalEtcCertificateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function digital_etc_certificate_renders_from_verification_code(): void
    {
        Storage::fake('local');

        $permit = Permit::factory()->create([
            'verification_code' => 'SVV-DIGITAL-ETC-001',
            'is_virtual_available' => true,
        ]);

        $response = $this->get(route('digital.certificates.show', $permit->verification_code));

        $response->assertOk()
            ->assertSee('Digital Emergency Travel Certificate')
            ->assertSee($permit->permit_no)
            ->assertSee($permit->verification_code)
            ->assertSee($permit->mrz_line_1)
            ->assertSee(route('verify.permit', $permit->verification_code));

        Storage::disk('local')->assertExists('qrcodes/'.$permit->permit_no.'.svg');

        $this->assertDatabaseHas('permit_verifications', [
            'permit_id' => $permit->id,
            'verification_code' => $permit->verification_code,
            'channel' => 'digital_certificate',
        ]);
    }

    #[Test]
    public function digital_etc_certificate_is_not_available_when_virtual_certificate_is_disabled(): void
    {
        $permit = Permit::factory()->create([
            'verification_code' => 'SVV-DIGITAL-DISABLED',
            'is_virtual_available' => false,
        ]);

        $this->get(route('digital.certificates.show', $permit->verification_code))
            ->assertNotFound();

        $this->assertSame(1, PermitVerification::query()->where('channel', 'digital_certificate')->count());
    }
}
