<?php
// FILE: database/seeders/PointOfEntrySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PointOfEntrySeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('airports') || ! Schema::hasTable('points_of_entry')) {
            return;
        }

        $airports = DB::table('airports')
            ->select('id', 'name', 'code')
            ->orderBy('id')
            ->get();

        foreach ($airports as $airport) {
            $exists = DB::table('points_of_entry')
                ->where('airport_id', $airport->id)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('points_of_entry')->insert([
                'airport_id' => $airport->id,
                'name' => 'Main Entry',
                'code' => 'MAIN',
                'is_active' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}