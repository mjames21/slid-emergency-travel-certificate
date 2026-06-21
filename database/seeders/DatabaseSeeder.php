<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AirportSeeder::class,
            CountrySeeder::class,
            DeskSeeder::class,
            StaffTitleSeeder::class,
            WorkflowTransitionSeeder::class,
            UserSeeder::class,
            DemoVisaApplicationSeeder::class,
            SystemSettingSeeder::class,
            PointOfEntrySeeder::class,
            NationalitySeeder::class,
            SexOptionSeeder::class,
            PurposeOfVisitSeeder::class,
            InternationalReadinessSeeder::class,
        ]);
    }
}
