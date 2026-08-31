<?php

namespace App\Services\Evisa;

use App\Enums\StaffTitleCode;
use App\Enums\VisaApplicationStatus;
use App\Models\Permit;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Documents\GeneratePermitService;
use App\Services\Notifications\SendPermitEmailService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApproveOnlineEvisaApplicationService
{
    public function __construct(
        protected GeneratePermitService $generatePermitService,
        protected SendPermitEmailService $sendPermitEmailService
    ) {}

    public function handle(VisaApplication $application, User $issuer): Permit
    {
        if (! self::canIssue($issuer)) {
            throw new RuntimeException('Only an ETC Issuer can approve and issue Emergency Travel Certificates.');
        }

        if ($application->application_channel !== VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE) {
            throw new RuntimeException('Only online Emergency Travel Certificate applications can be approved and issued through this workflow.');
        }

        [$permit, $created] = DB::transaction(function () use ($application, $issuer) {
            $lockedApplication = VisaApplication::query()
                ->with(['latestInvoice.payments', 'passenger', 'permit'])
                ->lockForUpdate()
                ->findOrFail($application->id);

            if ($lockedApplication->application_channel !== VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE) {
                throw new RuntimeException('Only online Emergency Travel Certificate applications can be approved and issued through this workflow.');
            }

            if ($lockedApplication->permit) {
                return [$lockedApplication->permit->fresh(['visaApplication.passenger']), false];
            }

            $hasSuccessfulPayment = $lockedApplication->latestInvoice?->payments()
                ->where('status', 'successful')
                ->exists() ?? false;

            if (! $hasSuccessfulPayment) {
                throw new RuntimeException('Emergency Travel Certificate application must be paid before approval and issue.');
            }

            $lockedApplication->update([
                'status' => VisaApplicationStatus::Approved,
                'reviewed_by' => $issuer->id,
                'approved_by' => $issuer->id,
                'reviewed_at' => now(),
                'approved_at' => now(),
                'last_status_changed_at' => now(),
            ]);

            $permit = $this->generatePermitService->handle($lockedApplication, $issuer);

            return [$permit->fresh(['visaApplication.passenger']), true];
        });

        if ($created) {
            $this->sendPermitEmailService->handle($permit);
        }

        return $permit->fresh(['visaApplication.passenger']);
    }

    public static function canIssue(?User $user): bool
    {
        return $user?->hasAnyStaffTitle(self::issuerStaffTitleCodes()) ?? false;
    }

    public static function issuerStaffTitleCodes(): array
    {
        return [
            StaffTitleCode::EtcIssuer->value,
        ];
    }
}
