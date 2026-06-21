<?php

namespace Tests\Feature;

use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffMfaEnforcementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function staff_user_without_confirmed_mfa_is_redirected_to_profile_security_settings(): void
    {
        config([
            'security.staff_mfa.required' => true,
            'security.staff_mfa.require_confirmed' => true,
        ]);

        $user = $this->staffUser();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('profile.show'));
    }

    #[Test]
    public function staff_user_with_confirmed_mfa_can_access_staff_systems(): void
    {
        config([
            'security.staff_mfa.required' => true,
            'security.staff_mfa.require_confirmed' => true,
        ]);

        $user = $this->staffUser([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['one-time-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('hq.emergency-travel-certificates.index'))
            ->assertOk();
    }

    #[Test]
    public function staff_mfa_requirement_returns_forbidden_for_json_requests(): void
    {
        config([
            'security.staff_mfa.required' => true,
            'security.staff_mfa.require_confirmed' => true,
        ]);

        $user = $this->staffUser();

        $this->actingAs($user)
            ->getJson(route('dashboard'))
            ->assertForbidden();
    }

    private function staffUser(array $overrides = []): User
    {
        $title = StaffTitle::query()->create([
            'name' => 'System Administrator',
            'code' => 'system_administrator',
            'description' => 'MFA enforcement test role',
            'active' => true,
        ]);

        $user = User::factory()->create(array_replace([
            'active' => true,
        ], $overrides));

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        return $user->fresh(['staffTitles']);
    }
}
