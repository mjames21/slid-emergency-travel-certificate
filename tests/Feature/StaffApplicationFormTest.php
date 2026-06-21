<?php

namespace Tests\Feature;

use App\Livewire\Staff\Applications\Create;
use App\Models\Airport;
use App\Models\Desk;
use App\Models\Passenger;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function travel_details_step_exposes_searchable_flight_carriers(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class)
            ->set('step', 3)
            ->assertSee('Flight Carrier')
            ->assertSee('staff-flight-carrier-list', false)
            ->assertSee('Air Sierra Leone')
            ->assertSee('Kenya Airways')
            ->assertSee('ASKY Airlines')
            ->assertDontSee('British Airways')
            ->assertDontSee('Ghana');
    }

    #[Test]
    public function traveler_history_appears_after_passport_detail_fields(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Create::class)
            ->set('step', 2)
            ->set('passport_number', 'SLRO92377')
            ->assertSee('<details', false)
            ->assertSee('View')
            ->assertSeeInOrder([
                'Passport Number',
                'Passport Expiry Year',
                'Date of Birth Day',
                'Traveler History',
                'Country of Birth',
            ]);
    }

    #[Test]
    public function staff_application_save_reuses_existing_passenger_identity(): void
    {
        $airport = Airport::factory()->create(['code' => 'FNA']);
        $desk = Desk::factory()->for($airport)->create();
        $user = User::factory()->create([
            'primary_airport_id' => $airport->id,
            'primary_desk_id' => $desk->id,
        ]);

        $passenger = Passenger::factory()->create([
            'surname' => 'JAMES',
            'given_names' => 'MOHAMED',
            'full_name' => 'JAMES MOHAMED',
            'nationality' => 'Sierra Leone',
            'nationality_code' => 'SLE',
            'passport_number' => 'SLRO92377',
            'passport_expiry_date' => '2029-03-12',
            'date_of_birth' => '1986-04-21',
        ]);

        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('airport_id', $airport->id)
            ->set('desk_id', $desk->id)
            ->set('surname', 'JAMES')
            ->set('given_names', 'MOHAMED')
            ->set('nationality', 'Sierra Leone')
            ->set('nationality_code', 'SLE')
            ->set('passport_number', 'slro92377')
            ->set('passport_expiry_year', '2029')
            ->set('passport_expiry_month', '03')
            ->set('passport_expiry_day', '12')
            ->set('sex', 'M')
            ->set('date_of_birth_year', '1986')
            ->set('date_of_birth_month', '04')
            ->set('date_of_birth_day', '21')
            ->set('country_of_birth', 'Sierra Leone')
            ->set('country_of_residence', 'Sierra Leone')
            ->set('email', 'mohamedjames21@gmail.com')
            ->set('phone', '078657484')
            ->set('passport_biodata_path', 'applications/passports/repeat-passport.jpg')
            ->set('purpose_of_visit', 'Business')
            ->set('period_of_stay_days', '30')
            ->set('arrival_date', '2026-06-16')
            ->set('valid_from', '2026-06-16')
            ->set('valid_until', '2026-07-16')
            ->set('flight_carrier', 'Kenya Airways')
            ->set('flight_number', 'KQ510')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(1, Passenger::query()->count());
        $this->assertDatabaseHas('passengers', [
            'id' => $passenger->id,
            'passport_number' => 'SLRO92377',
            'nationality' => 'Sierra Leone',
            'email' => 'mohamedjames21@gmail.com',
        ]);
        $this->assertSame(1, VisaApplication::query()->where('passenger_id', $passenger->id)->count());
    }
}
