<?php

namespace App\Policies;

use App\Models\Receipt;
use App\Models\User;

class ReceiptPolicy
{
    public function view(User $user, Receipt $receipt): bool
    {
        if ($user->hasAnyStaffTitle([
            'system_administrator',
            'hq_administrator',
            'compliance_auditor',
            'executive_observer',
        ])) {
            return true;
        }

        return $user->primary_airport_id === $receipt->payment->invoice->visaApplication->airport_id;
    }
}
