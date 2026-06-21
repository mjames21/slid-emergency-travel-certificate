<?php
// FILE: database/seeders/PurposeOfVisitSeeder.php

namespace Database\Seeders;

use App\Models\PurposeOfVisit;
use Illuminate\Database\Seeder;

class PurposeOfVisitSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'name' => 'Tourism',
                'code' => 'TOURISM',
                'description' => 'Holiday, sightseeing, leisure travel',
            ],
            [
                'name' => 'Business',
                'code' => 'BUSINESS',
                'description' => 'Meetings, conferences, commercial activity',
            ],
            [
                'name' => 'Official Visit',
                'code' => 'OFFICIAL',
                'description' => 'Government or diplomatic official travel',
            ],
            [
                'name' => 'Transit',
                'code' => 'TRANSIT',
                'description' => 'Short stay while connecting to another destination',
            ],
            [
                'name' => 'Family Visit',
                'code' => 'FAMILY',
                'description' => 'Visiting relatives or dependants',
            ],
            [
                'name' => 'Study',
                'code' => 'STUDY',
                'description' => 'Academic or educational purpose',
            ],
            [
                'name' => 'Medical',
                'code' => 'MEDICAL',
                'description' => 'Medical treatment or consultation',
            ],
            [
                'name' => 'Religious',
                'code' => 'RELIGIOUS',
                'description' => 'Religious event or mission',
            ],
            [
                'name' => 'Employment Discussion',
                'code' => 'EMPLOYMENT_DISCUSSION',
                'description' => 'Interviews or employment-related meetings',
            ],
            [
                'name' => 'Other',
                'code' => 'OTHER',
                'description' => 'Any other lawful purpose not listed above',
            ],
        ];

        $payload = collect($rows)
            ->values()
            ->map(fn (array $row, int $index) => [
                'name' => $row['name'],
                'code' => $row['code'],
                'description' => $row['description'],
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        PurposeOfVisit::query()->upsert(
            $payload,
            ['code'],
            ['name', 'description', 'is_active', 'sort_order', 'updated_at']
        );
    }
}