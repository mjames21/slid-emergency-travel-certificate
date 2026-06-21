<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_with_non_staff_email_domains_cannot_authenticate(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $user = User::factory()->create([
            'email' => 'officer@example.test',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_authenticated_non_staff_email_users_are_removed_from_staff_routes(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $this->actingAs(User::factory()->create([
            'email' => 'legacy@example.test',
        ]));

        $this->get('/dashboard')
            ->assertRedirect(route('login', absolute: false))
            ->assertSessionHasErrors(['email']);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_repeated_failed_login_attempts_are_rate_limited_for_the_same_account(): void
    {
        $user = User::factory()->create([
            'email' => 'officer@example.test',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
                ->post('/login', [
                    'email' => $user->email,
                    'password' => 'wrong-password',
                ])
                ->assertRedirect();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);
    }

    public function test_rotating_login_usernames_from_one_ip_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])
                ->post('/login', [
                    'email' => "rotating-{$attempt}@example.test",
                    'password' => 'wrong-password',
                ])
                ->assertRedirect();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.11'])
            ->post('/login', [
                'email' => 'rotating-final@example.test',
                'password' => 'wrong-password',
            ])
            ->assertStatus(429);
    }
}
