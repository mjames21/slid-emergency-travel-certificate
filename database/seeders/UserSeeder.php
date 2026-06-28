<?php

namespace Database\Seeders;

use App\Enums\StaffTitleCode;
use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $systemAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@immigration.gov.sl'],
            [
                'name' => 'System Administrator',
                'staff_number' => 'SLID-0001',
                'job_title' => 'System Administrator',
                'phone' => '+232000000001',
                'password' => Hash::make('ChangeMe123!'),
                'active' => true,
                'email_verified_at' => now(),
            ]
        );

        $title = StaffTitle::query()
            ->where('code', StaffTitleCode::SystemAdministrator->value)
            ->firstOrFail();

        $systemAdmin->staffTitles()->sync([
            $title->id => [
                'assigned_by_user_id' => $systemAdmin->id,
                'assigned_at' => now(),
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
