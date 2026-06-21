<?php

namespace Database\Seeders;

use App\Models\Airport;
use App\Models\Desk;
use Illuminate\Database\Seeder;

class DeskSeeder extends Seeder
{
    public function run(): void
    {
        $fna = Airport::query()->where('code', 'FNA')->firstOrFail();
        $boa = Airport::query()->where('code', 'BOA')->firstOrFail();

        $desks = [
            [
                'airport_id' => $fna->id,
                'name' => 'Visa on Arrival Desk 1',
                'code' => 'VOA-01',
                'location' => 'Arrival Hall',
                'description' => 'Primary visa issuance desk',
                'active' => true,
            ],
            [
                'airport_id' => $fna->id,
                'name' => 'Visa on Arrival Desk 2',
                'code' => 'VOA-02',
                'location' => 'Arrival Hall',
                'description' => 'Secondary visa issuance desk',
                'active' => true,
            ],
            [
                'airport_id' => $boa->id,
                'name' => 'Visa Desk 1',
                'code' => 'VOA-01',
                'location' => 'Terminal Entry',
                'description' => 'Regional visa desk',
                'active' => true,
            ],
        ];

        foreach ($desks as $desk) {
            Desk::query()->updateOrCreate(
                ['airport_id' => $desk['airport_id'], 'code' => $desk['code']],
                $desk
            );
        }
    }
}
