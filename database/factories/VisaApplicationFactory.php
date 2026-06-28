<?php

namespace Database\Factories;

use App\Enums\VisaApplicationStatus;
use App\Models\Passenger;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisaApplicationFactory extends Factory
{
    protected $model = VisaApplication::class;

    public function definition(): array
    {
        return [
            'application_no' => 'ETC-'.now()->format('Ymd').'-'.$this->faker->unique()->numberBetween(10000, 99999),
            'passenger_id' => Passenger::factory(),
            'created_by' => User::factory(),
            'submitted_by' => null,
            'approved_by' => null,
            'reviewed_by' => null,
            'visa_type' => VisaApplication::TYPE_EMERGENCY_TRAVEL_CERTIFICATE,
            'application_channel' => VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE,
            'status' => $this->faker->randomElement([
                VisaApplicationStatus::AwaitingPayment,
                VisaApplicationStatus::Paid,
                VisaApplicationStatus::Approved,
                VisaApplicationStatus::PermitIssued,
            ]),
            'purpose_of_visit' => strtoupper($this->faker->randomElement(['RETURN HOME', 'FAMILY EMERGENCY', 'LOST PASSPORT', 'OFFICIAL TRAVEL'])),
            'point_of_entry' => 'EMERGENCY TRAVEL CERTIFICATE DESK',
            'period_of_stay_days' => $this->faker->numberBetween(7, 30),
            'period_of_stay_text' => 'ONE (1) MONTH',
            'arrival_date' => now()->toDateString(),
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'flight_carrier' => strtoupper($this->faker->randomElement(['TURKISH AIRLINES', 'BRUSSELS AIRLINES', 'KENYA AIRWAYS', 'ASKY'])),
            'flight_number' => strtoupper($this->faker->bothify('##??')),
            'flight_details' => strtoupper($this->faker->company()),
            'host_name' => strtoupper($this->faker->company()),
            'host_address' => strtoupper($this->faker->address()),
            'host_phone' => $this->faker->phoneNumber(),
            'destination_address' => strtoupper($this->faker->address()),
            'remarks' => $this->faker->sentence(),
            'submitted_at' => now(),
            'reviewed_at' => null,
            'approved_at' => null,
            'last_status_changed_at' => now(),
        ];
    }
}
