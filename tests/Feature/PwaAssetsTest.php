<?php

namespace Tests\Feature;

use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PwaAssetsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function office_etc_entry_requires_login_and_includes_pwa_registration_for_issuer(): void
    {
        $this->get('/emergency-travel-certificate/apply')
            ->assertRedirect('/login');

        $this->actingAsStaffUserWithTitle('etc_issuer', 'ETC Issuer');

        $this->get('/emergency-travel-certificate/apply')
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('pwa-register.js', false);
    }

    #[Test]
    public function pwa_manifest_and_service_worker_are_configured_for_app_mode(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true);
        $serviceWorker = (string) file_get_contents(public_path('service-worker.js'));

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('SLID Emergency Travel Certificate', $manifest['name']);
        $this->assertSame('/dashboard', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotSame('fullscreen', $manifest['display']);
        $this->assertStringContainsString('/offline.html', $serviceWorker);
        $this->assertStringContainsString('request.mode === \'navigate\'', $serviceWorker);
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
