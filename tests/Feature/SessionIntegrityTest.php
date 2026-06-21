<?php

namespace Tests\Feature;

use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SessionIntegrityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authenticated_session_allows_same_browser_inside_same_network_prefix(): void
    {
        $user = $this->staffUser();

        $this->withServerVariables($this->client('203.0.113.10', 'SLID-Kiosk/1.0'))
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->withServerVariables($this->client('203.0.113.10', 'SLID-Kiosk/1.0'))
            ->get(route('hq.emergency-travel-certificates.index'))
            ->assertOk();

        $this->withServerVariables($this->client('203.0.113.88', 'SLID-Kiosk/1.0'))
            ->get(route('hq.emergency-travel-certificates.index'))
            ->assertOk();

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function authenticated_session_is_invalidated_when_browser_fingerprint_changes(): void
    {
        $user = $this->staffUser();

        $this->withServerVariables($this->client('203.0.113.20', 'SLID-Kiosk/1.0'))
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->withServerVariables($this->client('203.0.113.20', 'SLID-Kiosk/1.0'))
            ->get(route('hq.emergency-travel-certificates.index'))
            ->assertOk();

        $this->withServerVariables($this->client('203.0.113.20', 'Unknown-Browser/9.9'))
            ->get(route('hq.emergency-travel-certificates.index'))
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function staffUser(): User
    {
        $title = StaffTitle::query()->create([
            'name' => 'System Administrator',
            'code' => 'system_administrator',
            'description' => 'Security test role',
            'active' => true,
        ]);

        $user = User::factory()->create([
            'active' => true,
        ]);

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        return $user;
    }

    private function client(string $ip, string $userAgent): array
    {
        return [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => $userAgent,
        ];
    }
}
