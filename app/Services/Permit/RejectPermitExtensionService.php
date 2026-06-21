<?php

// FILE: app/Services/Permit/RejectPermitExtensionService.php

namespace App\Services\Permit;

use App\Models\PermitExtension;
use App\Models\User;
use App\Services\Audit\WriteAuditLogService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RejectPermitExtensionService
{
    public function __construct(
        protected WriteAuditLogService $writeAuditLogService
    ) {
    }

    public function handle(User $user, PermitExtension $permitExtension, ?string $decisionNote = null): PermitExtension
    {
        if ($permitExtension->status !== 'pending') {
            throw new RuntimeException('Only pending extension requests can be rejected.');
        }

        return DB::transaction(function () use ($user, $permitExtension, $decisionNote) {
            $permitExtension->status = 'rejected';
            $permitExtension->rejected_at = now();
            $permitExtension->rejected_by = $user->id;
            $permitExtension->decision_note = filled($decisionNote) ? trim($decisionNote) : $permitExtension->decision_note;
            $permitExtension->save();

            $this->writeAuditLogService->handle(
                $user,
                'permit_extension.rejected',
                $permitExtension,
                [
                    'extension_no' => $permitExtension->extension_no,
                    'original_permit_id' => $permitExtension->original_permit_id,
                    'requested_new_valid_until' => optional($permitExtension->requested_new_valid_until)?->toDateString(),
                ],
                'Permit extension rejected'
            );

            return $permitExtension->fresh([
                'originalPermit',
                'newPermit',
                'visaApplication',
                'passenger',
                'requester',
                'approver',
                'rejector',
            ]);
        });
    }
}