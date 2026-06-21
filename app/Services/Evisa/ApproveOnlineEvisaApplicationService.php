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

    public function handle(VisaApplication $application, User $approver): Permit
    {
        if (! self::canIssue($approver)) {
            throw new RuntimeException('Only ETC issuers, executives, and system administrators can issue Emergency Travel Certificates.');
        }

        if ($application->application_channel !== VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE) {
            throw new RuntimeException('Only online Emergency Travel Certificate applications can be approved through this workflow.');
        }

        $application->loadMissing(['latestInvoice.payments', 'passenger']);

        $hasSuccessfulPayment = $application->latestInvoice?->payments()
            ->where('status', 'successful')
            ->exists() ?? false;

        if (! $hasSuccessfulPayment) {
            throw new RuntimeException('Emergency Travel Certificate application must be paid before HQ approval.');
        }

        return DB::transaction(function () use ($application, $approver) {
            $application->update([
                'status' => VisaApplicationStatus::Approved,
                'reviewed_by' => $approver->id,
                'approved_by' => $approver->id,
                'reviewed_at' => now(),
                'approved_at' => now(),
                'last_status_changed_at' => now(),
            ]);

            $permit = $this->generatePermitService->handle($application->fresh(['latestInvoice.payments', 'latestWaiverApproval']), $approver);

            $this->sendPermitEmailService->handle($permit->fresh(['visaApplication.passenger']));

            return $permit->fresh(['visaApplication.passenger']);
        });
    }

    public static function canIssue(?User $user): bool
    {
        return $user?->hasAnyStaffTitle(self::issuerStaffTitleCodes()) ?? false;
    }

    public static function issuerStaffTitleCodes(): array
    {
        return [
            StaffTitleCode::EtcIssuer->value,
            StaffTitleCode::ExecutiveObserver->value,
            StaffTitleCode::SystemAdministrator->value,
        ];
    }
}
