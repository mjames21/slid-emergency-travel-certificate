<?php

namespace Database\Factories;

use App\Models\Airport;
use App\Models\Desk;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeskFactory extends Factory
{
    protected $model = Desk::class;

    public function definition(): array
    {
        return [
            'airport_id' => Airport::factory(),
            'name' => 'Visa Desk ' . $this->faker->numberBetween(1, 9),
            'code' => 'VOA-' . $this->faker->unique()->numberBetween(1, 99),
            'location' => $this->faker->randomElement(['Arrival Hall', 'Terminal A', 'Terminal B']),
            'description' => 'Visa on Arrival processing desk',
            'active' => true,
        ];
    }
}
