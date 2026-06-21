<?php

// FILE: app/Services/Permit/RequestPermitExtensionService.php

namespace App\Services\Permit;

use App\Models\Permit;
use App\Models\PermitExtension;
use App\Models\User;
use App\Services\Audit\WriteAuditLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RequestPermitExtensionService
{
    public function __construct(
        protected CanExtendPermitService $canExtendPermitService,
        protected WriteAuditLogService $writeAuditLogService
    ) {
    }

    public function handle(User $user, Permit $permit, array $data): PermitExtension
    {
        $eligibility = $this->canExtendPermitService->handle($permit);

        if (! $eligibility['allowed']) {
            throw new RuntimeException($eligibility['message'] ?? 'This permit cannot be extended.');
        }

        if (! $permit->valid_until) {
            throw new RuntimeException('This permit does not have a valid-until date.');
        }

        return DB::transaction(function () use ($user, $permit, $data) {
            $currentValidUntil = Carbon::parse($permit->valid_until);
            $requestedExtraDays = max((int) ($data['requested_extra_days'] ?? 0), 1);
            $requestedNewValidUntil = $currentValidUntil->copy()->addDays($requestedExtraDays);

            $permitExtension = PermitExtension::query()->create([
                'original_permit_id' => $permit->id,
                'new_permit_id' => null,
                'visa_application_id' => $permit->visa_application_id,
                'passenger_id' => $permit->visaApplication->passenger->id,
                'extension_no' => $this->generateExtensionNumber($user),
                'requested_extra_days' => $requestedExtraDays,
                'current_valid_until' => $currentValidUntil->toDateString(),
                'requested_new_valid_until' => $requestedNewValidUntil->toDateString(),
                'reason_code' => filled($data['reason_code'] ?? null) ? trim((string) $data['reason_code']) : null,
                'reason' => trim((string) $data['reason']),
                'is_fee_waived' => (bool) ($data['is_fee_waived'] ?? false),
                'fee_amount' => (float) ($data['fee_amount'] ?? 0),
                'status' => 'pending',
                'requested_at' => now(),
                'requested_by' => $user->id,
                'decision_note' => filled($data['decision_note'] ?? null) ? trim((string) $data['decision_note']) : null,
            ]);

            $this->writeAuditLogService->handle(
                $user,
                'permit_extension.requested',
                $permitExtension,
                [
                    'extension_no' => $permitExtension->extension_no,
                    'original_permit_id' => $permit->id,
                    'original_permit_no' => $permit->permit_no,
                    'requested_extra_days' => $requestedExtraDays,
                    'requested_new_valid_until' => $requestedNewValidUntil->toDateString(),
                    'reason_code' => $permitExtension->reason_code,
                    'is_fee_waived' => $permitExtension->is_fee_waived,
                    'fee_amount' => $permitExtension->fee_amount,
                ],
                'Permit extension request created'
            );

            return $permitExtension;
        });
    }

    protected function generateExtensionNumber(User $user): string
    {
        $airportCode = strtoupper($user->primaryAirport?->code ?: 'UNK');
        $date = now()->format('Ymd');
        $prefix = "EXT-{$airportCode}-{$date}-";

        $countToday = PermitExtension::query()
            ->where('extension_no', 'like', $prefix . '%')
            ->count();

        $sequence = str_pad((string) ($countToday + 1), 4, '0', STR_PAD_LEFT);

        return $prefix . $sequence;
    }
}
