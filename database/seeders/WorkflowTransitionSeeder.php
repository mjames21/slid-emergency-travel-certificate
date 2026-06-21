<?php

namespace Database\Seeders;

use App\Models\StaffTitle;
use App\Models\StaffTitleWorkflowTransition;
use Illuminate\Database\Seeder;

class WorkflowTransitionSeeder extends Seeder
{
    public function run(): void
    {
        $map = [
            'visa_processing_officer' => [
                ['from' => 'draft', 'action' => 'submit', 'to' => 'submitted', 'checker' => false],
                ['from' => 'submitted', 'action' => 'send_to_billing', 'to' => 'awaiting_payment', 'checker' => false],
                ['from' => 'approved', 'action' => 'prepare_permit', 'to' => 'permit_ready', 'checker' => false],
                ['from' => 'permit_ready', 'action' => 'issue_permit', 'to' => 'permit_issued', 'checker' => false],
            ],
            'payment_officer' => [
                ['from' => 'awaiting_payment', 'action' => 'mark_payment_pending', 'to' => 'payment_pending', 'checker' => false],
                ['from' => 'payment_pending', 'action' => 'confirm_payment', 'to' => 'paid', 'checker' => false],
            ],
            'shift_supervisor' => [
                ['from' => 'submitted', 'action' => 'send_under_review', 'to' => 'under_review', 'checker' => false],
                ['from' => 'under_review', 'action' => 'approve', 'to' => 'approved', 'checker' => false],
                ['from' => 'under_review', 'action' => 'cancel', 'to' => 'cancelled', 'checker' => false],
                ['from' => 'submitted', 'action' => 'approve_waiver_case', 'to' => 'under_review', 'checker' => true],
                ['from' => 'permit_issued', 'action' => 'authorize_reprint', 'to' => 'permit_issued', 'checker' => true],
            ],
            'airport_manager' => [
                ['from' => 'under_review', 'action' => 'approve', 'to' => 'approved', 'checker' => false],
                ['from' => 'permit_issued', 'action' => 'revoke', 'to' => 'revoked', 'checker' => true],
                ['from' => 'permit_issued', 'action' => 'cancel', 'to' => 'cancelled', 'checker' => true],
            ],
            'hq_administrator' => [
                ['from' => 'permit_issued', 'action' => 'revoke', 'to' => 'revoked', 'checker' => true],
                ['from' => 'paid', 'action' => 'force_under_review', 'to' => 'under_review', 'checker' => true],
            ],
            'system_administrator' => [
                ['from' => 'draft', 'action' => 'submit', 'to' => 'submitted', 'checker' => false],
                ['from' => 'submitted', 'action' => 'approve', 'to' => 'approved', 'checker' => false],
                ['from' => 'approved', 'action' => 'issue_permit', 'to' => 'permit_issued', 'checker' => false],
            ],
        ];

        foreach ($map as $titleCode => $transitions) {
            $title = StaffTitle::query()->where('code', $titleCode)->firstOrFail();

            foreach ($transitions as $index => $transition) {
                StaffTitleWorkflowTransition::query()->updateOrCreate(
                    [
                        'staff_title_id' => $title->id,
                        'from_status_key' => $transition['from'],
                        'action' => $transition['action'],
                        'to_status_key' => $transition['to'],
                    ],
                    [
                        'sort_order' => $index + 1,
                        'requires_reason' => in_array($transition['action'], ['cancel', 'revoke', 'authorize_reprint'], true),
                        'requires_checker' => $transition['checker'],
                        'active' => true,
                    ]
                );
            }
        }
    }
}
