<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;
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
}
