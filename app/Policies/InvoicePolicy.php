<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->hasAnyStaffTitle([
            'system_administrator',
            'hq_administrator',
            'compliance_auditor',
            'executive_observer',
        ])) {
            return true;
        }

        return $user->primary_airport_id === $invoice->visaApplication->airport_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyStaffTitle([
            'system_administrator',
            'airport_manager',
            'shift_supervisor',
            'visa_processing_officer',
            'payment_officer',
        ]);
    }
}
