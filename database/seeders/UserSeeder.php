<?php

namespace Database\Seeders;

use App\Models\Airport;
use App\Models\Desk;
use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $fna = Airport::query()->where('code', 'FNA')->firstOrFail();
        $boa = Airport::query()->where('code', 'BOA')->firstOrFail();

        $fnaDesk1 = Desk::query()->where('airport_id', $fna->id)->where('code', 'VOA-01')->firstOrFail();
        $fnaDesk2 = Desk::query()->where('airport_id', $fna->id)->where('code', 'VOA-02')->firstOrFail();
        $boaDesk1 = Desk::query()->where('airport_id', $boa->id)->where('code', 'VOA-01')->firstOrFail();

        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@immigration.gov.sl',
                'staff_number' => 'SLID-0001',
                'job_title' => 'System Administrator',
                'phone' => '+232000000001',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['system_administrator'],
            ],
            [
                'name' => 'HQ Administrator',
                'email' => 'hq@immigration.gov.sl',
                'staff_number' => 'SLID-0002',
                'job_title' => 'HQ Administrator',
                'phone' => '+232000000002',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['hq_administrator'],
            ],
            [
                'name' => 'FNA Airport Manager',
                'email' => 'manager.fna@immigration.gov.sl',
                'staff_number' => 'SLID-0003',
                'job_title' => 'Airport Manager',
                'phone' => '+232000000003',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['airport_manager'],
            ],
            [
                'name' => 'FNA Shift Supervisor',
                'email' => 'supervisor.fna@immigration.gov.sl',
                'staff_number' => 'SLID-0004',
                'job_title' => 'Shift Supervisor',
                'phone' => '+232000000004',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['shift_supervisor'],
            ],
            [
                'name' => 'VOA Officer One',
                'email' => 'officer1.fna@immigration.gov.sl',
                'staff_number' => 'SLID-0005',
                'job_title' => 'Visa Processing Officer',
                'phone' => '+232000000005',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['visa_processing_officer'],
            ],
            [
                'name' => 'VOA Officer Two',
                'email' => 'officer2.fna@immigration.gov.sl',
                'staff_number' => 'SLID-0006',
                'job_title' => 'Visa Processing Officer',
                'phone' => '+232000000006',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk2->id,
                'titles' => ['visa_processing_officer'],
            ],
            [
                'name' => 'NRA Payment Officer',
                'email' => 'payment.fna@immigration.gov.sl',
                'staff_number' => 'SLID-0007',
                'job_title' => 'NRA Payment Officer',
                'phone' => '+232000000007',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['payment_officer'],
            ],
            [
                'name' => 'Compliance Auditor',
                'email' => 'audit@immigration.gov.sl',
                'staff_number' => 'SLID-0008',
                'job_title' => 'Compliance Auditor',
                'phone' => '+232000000008',
                'primary_airport_id' => $fna->id,
                'primary_desk_id' => $fnaDesk1->id,
                'titles' => ['compliance_auditor'],
            ],
            [
                'name' => 'Regional Officer',
                'email' => 'officer.boa@immigration.gov.sl',
                'staff_number' => 'SLID-0009',
                'job_title' => 'Visa Processing Officer',
                'phone' => '+232000000009',
                'primary_airport_id' => $boa->id,
                'primary_desk_id' => $boaDesk1->id,
                'titles' => ['visa_processing_officer'],
            ],
        ];

        foreach ($users as $data) {
            $titles = $data['titles'];
            unset($data['titles']);

            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'password' => Hash::make('ChangeMe123!'),
                    'active' => true,
                    'email_verified_at' => now(),
                ])
            );

            $titleIds = StaffTitle::query()->whereIn('code', $titles)->pluck('id')->all();

            $syncData = [];
            foreach ($titleIds as $index => $titleId) {
                $syncData[$titleId] = [
                    'assigned_by_user_id' => $user->id,
                    'assigned_at' => now(),
                    'is_primary' => $index === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $user->staffTitles()->sync($syncData);

            $user->airports()->syncWithoutDetaching([
                $user->primary_airport_id => [
                    'desk_id' => $user->primary_desk_id,
                    'is_primary' => true,
                    'assigned_at' => now(),
                    'assigned_by_user_id' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }
}
