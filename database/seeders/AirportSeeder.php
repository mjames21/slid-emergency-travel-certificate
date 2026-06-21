<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        $airports = [
            [
                'name' => 'Freetown International Airport',
                'code' => 'FNA',
                'city' => 'Freetown',
                'country' => 'Sierra Leone',
                'timezone' => 'Africa/Freetown',
                'active' => true,
            ],
            [
                'name' => 'Bo Regional Airstrip',
                'code' => 'BOA',
                'city' => 'Bo',
                'country' => 'Sierra Leone',
                'timezone' => 'Africa/Freetown',
                'active' => true,
            ],
        ];

        foreach ($airports as $airport) {
            Airport::query()->updateOrCreate(
                ['code' => $airport['code']],
                $airport
            );
        }
    }
}
