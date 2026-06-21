<?php

namespace Database\Factories;

use App\Models\Passenger;
use Illuminate\Database\Eloquent\Factories\Factory;

class PassengerFactory extends Factory
{
    protected $model = Passenger::class;

    public function definition(): array
    {
        $surname = strtoupper($this->faker->lastName());
        $givenNames = strtoupper($this->faker->firstName() . ' ' . $this->faker->firstName());

        return [
            'surname' => $surname,
            'given_names' => $givenNames,
            'full_name' => trim($surname . ' ' . $givenNames),
            'nationality' => $this->faker->randomElement([
                'Turkish',
                'Ghanaian',
                'Nigerian',
                'Kenyan',
                'British',
                'Indian',
                'American',
            ]),
            'nationality_code' => $this->faker->randomElement(['TUR', 'GHA', 'NGA', 'KEN', 'GBR', 'IND', 'USA']),
            'passport_number' => strtoupper($this->faker->unique()->bothify('U########')),
            'passport_expiry_date' => now()->addYears(3)->toDateString(),
            'sex' => $this->faker->randomElement(['M', 'F']),
            'date_of_birth' => $this->faker->date(),
            'country_of_birth' => $this->faker->country(),
            'country_of_residence' => $this->faker->country(),
            'occupation' => strtoupper($this->faker->jobTitle()),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
