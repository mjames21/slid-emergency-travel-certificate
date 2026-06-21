<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\Permit;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Evisa\ApproveOnlineEvisaApplicationService;

class PermitPolicy
{
    public function view(User $user, Permit $permit): bool
    {
        if ($user->hasAnyStaffTitle([
            'system_administrator',
            'hq_administrator',
            'compliance_auditor',
            'executive_observer',
        ])) {
            return true;
        }

        return $user->primary_airport_id === $permit->visaApplication->airport_id;
    }

    public function print(User $user, Permit $permit): bool
    {
        if ($this->isOnlineEmergencyTravelCertificate($permit)) {
            return ApproveOnlineEvisaApplicationService::canIssue($user)
                && $this->hasSuccessfulPayment($permit);
        }

        return $this->view($user, $permit)
            && $user->hasAnyStaffTitle([
                'system_administrator',
                'airport_manager',
                'shift_supervisor',
                'visa_processing_officer',
            ]);
    }

    public function revoke(User $user, Permit $permit): bool
    {
        return $user->hasAnyStaffTitle([
            'system_administrator',
            'hq_administrator',
            'compliance_auditor',
            'airport_manager',
        ]);
    }

    private function isOnlineEmergencyTravelCertificate(Permit $permit): bool
    {
        return $permit->visaApplication?->application_channel === VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE;
    }

    private function hasSuccessfulPayment(Permit $permit): bool
    {
        if ($permit->payment?->status === PaymentStatus::Successful) {
            return true;
        }

        return $permit->visaApplication?->latestInvoice?->payments()
            ->where('status', PaymentStatus::Successful->value)
            ->exists() ?? false;
    }
}
