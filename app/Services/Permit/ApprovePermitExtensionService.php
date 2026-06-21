<?php
// FILE: app/Services/Permit/ApprovePermitExtensionService.php

namespace App\Services\Permit;

use App\Models\Permit;
use App\Models\PermitExtension;
use App\Models\User;
use App\Services\Audit\WriteAuditLogService;
use App\Support\PermitLifecycleStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ApprovePermitExtensionService
{
    public function __construct(
        protected WriteAuditLogService $writeAuditLogService
    ) {
    }

    public function handle(User $user, PermitExtension $permitExtension, ?string $decisionNote = null): PermitExtension
    {
        if ($permitExtension->status !== 'pending') {
            throw new RuntimeException('Only pending extension requests can be approved.');
        }

        return DB::transaction(function () use ($user, $permitExtension, $decisionNote) {
            $permitExtension->loadMissing([
                'originalPermit',
                'visaApplication',
                'passenger',
            ]);

            $originalPermit = $permitExtension->originalPermit;

            if (! $originalPermit) {
                throw new RuntimeException('Original permit not found.');
            }

            $newPermit = $this->createLinkedPermit($user, $originalPermit, $permitExtension);

            PermitLifecycleStatus::set($originalPermit, 'extended');
            $originalPermit->save();

            $permitExtension->status = 'approved';
            $permitExtension->approved_at = now();
            $permitExtension->approved_by = $user->id;
            $permitExtension->decision_note = filled($decisionNote) ? trim($decisionNote) : $permitExtension->decision_note;
            $permitExtension->new_permit_id = $newPermit->id;
            $permitExtension->save();

            $this->writeAuditLogService->handle(
                $user,
                'permit_extension.approved',
                $permitExtension,
                [
                    'extension_no' => $permitExtension->extension_no,
                    'original_permit_id' => $originalPermit->id,
                    'original_permit_no' => $originalPermit->permit_no,
                    'new_permit_id' => $newPermit->id,
                    'new_permit_no' => $newPermit->permit_no,
                    'requested_new_valid_until' => optional($permitExtension->requested_new_valid_until)?->toDateString(),
                ],
                'Permit extension approved'
            );

            $this->writeAuditLogService->handle(
                $user,
                'permit.issued_extension',
                $newPermit,
                [
                    'parent_permit_id' => $originalPermit->id,
                    'parent_permit_no' => $originalPermit->permit_no,
                    'extension_id' => $permitExtension->id,
                    'extension_no' => $permitExtension->extension_no,
                ],
                'Extension permit issued'
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

    protected function createLinkedPermit(User $user, Permit $originalPermit, PermitExtension $permitExtension): Permit
    {
        $newPermit = $originalPermit->replicate();

        $newPermit->parent_permit_id = $originalPermit->id;
        $newPermit->is_extension = true;
        PermitLifecycleStatus::set($newPermit, 'active');
        $newPermit->permit_no = $this->generatePermitNumber($user);
        $newPermit->valid_until = $permitExtension->requested_new_valid_until;

        if ($this->hasAttribute($newPermit, 'verification_code')) {
            $newPermit->verification_code = Str::upper(Str::random(12));
        }

        if ($this->hasAttribute($newPermit, 'security_seal')) {
            $newPermit->security_seal = strtoupper(substr(hash('sha256', $newPermit->permit_no . now()->timestamp . Str::uuid()), 0, 32));
        }

        if ($this->hasAttribute($newPermit, 'issuer_id')) {
            $newPermit->issuer_id = $user->id;
        }

        if ($this->hasAttribute($newPermit, 'issued_at')) {
            $newPermit->issued_at = now();
        }

        if ($this->hasAttribute($newPermit, 'print_count')) {
            $newPermit->print_count = 0;
        }

        if ($this->hasAttribute($newPermit, 'last_printed_at')) {
            $newPermit->last_printed_at = null;
        }

        $newPermit->save();

        return $newPermit;
    }

    protected function generatePermitNumber(User $user): string
    {
        $airportCode = strtoupper($user->primaryAirport?->code ?: 'UNK');
        $date = now()->format('Ymd');
        $prefix = "SVA-{$airportCode}-{$date}-";

        $countToday = Permit::query()
            ->where('permit_no', 'like', $prefix . '%')
            ->count();

        $sequence = str_pad((string) ($countToday + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }

    protected function hasAttribute(Permit $permit, string $attribute): bool
    {
        return array_key_exists($attribute, $permit->getAttributes());
    }
}
