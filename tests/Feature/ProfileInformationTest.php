<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
use Laravel\Passkeys\Contracts\PasskeyUser;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_profile_information_is_available(): void
    {
        $this->actingAs($user = User::factory()->create());

        $component = Livewire::test(UpdateProfileInformationForm::class);

        $this->assertEquals($user->name, $component->state['name']);
        $this->assertEquals($user->email, $component->state['email']);
    }

    public function test_profile_information_can_be_updated(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $this->actingAs($user = User::factory()->create());

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['name' => 'Test Name', 'email' => 'test@immigration.gov.sl'])
            ->call('updateProfileInformation');

        $this->assertEquals('Test Name', $user->fresh()->name);
        $this->assertEquals('test@immigration.gov.sl', $user->fresh()->email);
    }

    public function test_profile_email_must_use_staff_domain(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $this->actingAs($user = User::factory()->create([
            'email' => 'current@immigration.gov.sl',
        ]));

        Livewire::test(UpdateProfileInformationForm::class)
            ->set('state', ['name' => 'Test Name', 'email' => 'external@example.com'])
            ->call('updateProfileInformation')
            ->assertHasErrors(['email']);

        $this->assertEquals('current@immigration.gov.sl', $user->fresh()->email);
    }

    public function test_user_model_supports_passkeys(): void
    {
        $this->assertInstanceOf(PasskeyUser::class, User::factory()->create());
    }

    public function test_profile_shows_passkey_management(): void
    {
        $this->actingAs($user = User::factory()->create());

        $user->passkeys()->create([
            'name' => 'Office MacBook',
            'credential_id' => 'test-credential-id',
            'credential' => [
                'aaguid' => '00000000-0000-0000-0000-000000000000',
            ],
            'last_used_at' => now(),
        ]);

        $this->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Passkeys')
            ->assertSee('Register Passkey')
            ->assertSee('Office MacBook')
            ->assertSee('data-passkey-registration', false);
    }
}
