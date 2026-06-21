<?php

namespace Database\Seeders;

use App\Models\SexOption;
use Illuminate\Database\Seeder;

class SexOptionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $rows = [
            [
                'name' => 'Male',
                'code' => 'M',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Female',
                'code' => 'F',
                'is_active' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Unspecified',
                'code' => 'X',
                'is_active' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        SexOption::query()->upsert(
            $rows,
            ['code'],
            ['name', 'is_active', 'sort_order', 'updated_at']
        );
    }
}