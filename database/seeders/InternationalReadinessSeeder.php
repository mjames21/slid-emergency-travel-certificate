<?php

namespace Database\Seeders;

use App\Models\PolicyApproval;
use App\Models\TravelRequirementRule;
use Illuminate\Database\Seeder;

class InternationalReadinessSeeder extends Seeder
{
    public function run(): void
    {
        TravelRequirementRule::updateOrCreate(
            [
                'source' => 'sl_immigration',
                'nationality_code' => null,
                'visa_type' => 'visa_on_arrival',
                'purpose_of_visit' => null,
                'carrier_code' => null,
            ],
            [
                'document_type' => 'passport',
                'max_stay_days' => 30,
                'min_passport_validity_days' => 0,
                'visa_required' => true,
                'return_ticket_required' => false,
                'host_address_required' => true,
                'active' => true,
                'effective_from' => now()->toDateString(),
                'notes' => 'Baseline local rule. Replace or narrow by nationality/purpose when SL Immigration policy matrix is approved.',
            ]
        );

        foreach ([
            ['icao_doc_9303', 'ICAO Doc 9303 MRZ, document security, and travel document controls'],
            ['iata_timatic_rules', 'IATA/Timatic-style entry requirement verification and carrier movement context'],
            ['iom_migration_management', 'IOM-aligned migration management, referral, and protection workflow'],
            ['security_accreditation', 'Security hardening, penetration testing, access review, and disaster recovery'],
        ] as [$area, $summary]) {
            PolicyApproval::updateOrCreate(
                [
                    'policy_area' => $area,
                    'version' => '1.0',
                ],
                [
                    'standard_reference' => strtoupper(str_replace('_', ' ', $area)),
                    'status' => 'pending',
                    'summary' => $summary,
                    'evidence' => [
                        'system_controls' => [
                            'audit_logs',
                            'admissibility_screening',
                            'border_movements',
                            'permit_verification',
                        ],
                    ],
                    'requested_at' => now(),
                ]
            );
        }
    }
}
