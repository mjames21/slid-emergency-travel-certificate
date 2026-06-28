<?php

namespace Database\Seeders;

use App\Enums\StaffTitleCode;
use App\Models\StaffTitle;
use Illuminate\Database\Seeder;

class StaffTitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            [
                'name' => 'System Administrator',
                'code' => StaffTitleCode::SystemAdministrator->value,
                'description' => 'Creates and manages ETC Issuer and Executive users.',
                'active' => true,
            ],
            [
                'name' => 'ETC Issuer',
                'code' => StaffTitleCode::EtcIssuer->value,
                'description' => 'Issues and prints paid Emergency Travel Certificates.',
                'active' => true,
            ],
            [
                'name' => 'Executive',
                'code' => StaffTitleCode::ExecutiveObserver->value,
                'description' => 'Executive oversight for Emergency Travel Certificate applications.',
                'active' => true,
            ],
        ];

        foreach ($titles as $title) {
            StaffTitle::query()->updateOrCreate(
                ['code' => $title['code']],
                $title
            );
        }
    }
}
