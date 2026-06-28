<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Models\Permit;
use App\Models\User;
use App\Services\Evisa\ApproveOnlineEvisaApplicationService;

class PermitPolicy
{
    public function view(User $user, Permit $permit): bool
    {
        return ApproveOnlineEvisaApplicationService::canIssue($user);
    }

    public function print(User $user, Permit $permit): bool
    {
        return $this->view($user, $permit)
            && $this->hasSuccessfulPayment($permit);
    }

    public function revoke(User $user, Permit $permit): bool
    {
        return false;
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
