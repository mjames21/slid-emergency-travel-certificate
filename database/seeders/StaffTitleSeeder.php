<?php

namespace Database\Seeders;

use App\Models\StaffTitle;
use Illuminate\Database\Seeder;

class StaffTitleSeeder extends Seeder
{
    public function run(): void
    {
        $titles = [
            [
                'name' => 'System Administrator',
                'code' => 'system_administrator',
                'description' => 'Platform-wide technical administration',
                'allowed_statuses' => null,
                'can_view_all' => true,
                'can_invite_staff' => true,
                'can_approve_waiver' => true,
                'can_authorize_reprint' => true,
                'can_revoke_permit' => true,
                'can_manage_devices' => true,
                'active' => true,
            ],
            [
                'name' => 'ETC Issuer',
                'code' => 'etc_issuer',
                'description' => 'Authorized Emergency Travel Certificate issuance officer',
                'allowed_statuses' => ['paid', 'approved', 'permit_issued'],
                'can_view_all' => true,
                'can_invite_staff' => false,
                'can_approve_waiver' => false,
                'can_authorize_reprint' => false,
                'can_revoke_permit' => false,
                'can_manage_devices' => false,
                'active' => true,
            ],
            [
                'name' => 'HQ Administrator',
                'code' => 'hq_administrator',
                'description' => 'Headquarters operational oversight',
                'allowed_statuses' => null,
                'can_view_all' => true,
                'can_invite_staff' => true,
                'can_approve_waiver' => true,
                'can_authorize_reprint' => true,
                'can_revoke_permit' => true,
                'can_manage_devices' => false,
                'active' => true,
            ],
            [
                'name' => 'Airport Manager',
                'code' => 'airport_manager',
                'description' => 'Airport command oversight',
                'allowed_statuses' => ['submitted', 'awaiting_payment', 'paid', 'under_review', 'approved', 'permit_ready', 'permit_issued'],
                'can_view_all' => false,
                'can_invite_staff' => false,
                'can_approve_waiver' => true,
                'can_authorize_reprint' => true,
                'can_revoke_permit' => true,
                'can_manage_devices' => true,
                'active' => true,
            ],
            [
                'name' => 'Shift Supervisor',
                'code' => 'shift_supervisor',
                'description' => 'Operational checker and approval control',
                'allowed_statuses' => ['submitted', 'awaiting_payment', 'paid', 'under_review', 'approved', 'permit_ready'],
                'can_view_all' => false,
                'can_invite_staff' => false,
                'can_approve_waiver' => true,
                'can_authorize_reprint' => true,
                'can_revoke_permit' => false,
                'can_manage_devices' => false,
                'active' => true,
            ],
            [
                'name' => 'Visa Processing Officer',
                'code' => 'visa_processing_officer',
                'description' => 'Frontline visa application processing',
                'allowed_statuses' => ['draft', 'submitted', 'awaiting_payment', 'paid', 'permit_ready', 'permit_issued'],
                'can_view_all' => false,
                'can_invite_staff' => false,
                'can_approve_waiver' => false,
                'can_authorize_reprint' => false,
                'can_revoke_permit' => false,
                'can_manage_devices' => false,
                'active' => true,
            ],
            [
                'name' => 'Payment Officer',
                'code' => 'payment_officer',
                'description' => 'NRA payment desk receipt review and confirmation',
                'allowed_statuses' => ['awaiting_payment', 'payment_pending', 'paid'],
                'can_view_all' => false,
                'can_invite_staff' => false,
                'can_approve_waiver' => false,
                'can_authorize_reprint' => false,
                'can_revoke_permit' => false,
                'can_manage_devices' => false,
                'active' => true,
            ],
            [
                'name' => 'Compliance Auditor',
                'code' => 'compliance_auditor',
                'description' => 'Read-only audit and investigation',
                'allowed_statuses' => null,
                'can_view_all' => true,
                'can_invite_staff' => false,
                'can_approve_waiver' => false,
                'can_authorize_reprint' => false,
                'can_revoke_permit' => false,
                'can_manage_devices' => false,
                'active' => true,
            ],
            [
                'name' => 'Executive',
                'code' => 'executive_observer',
                'description' => 'Executive oversight and Emergency Travel Certificate issuance authority',
                'allowed_statuses' => null,
                'can_view_all' => true,
                'can_invite_staff' => false,
                'can_approve_waiver' => false,
                'can_authorize_reprint' => false,
                'can_revoke_permit' => false,
                'can_manage_devices' => false,
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
