<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Workflow\WorkflowService;

class VisaApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isActive() && $user->staffTitles()->exists();
    }

    public function view(User $user, VisaApplication $application): bool
    {
        if ($user->hasAnyStaffTitle([
            'system_administrator',
            'hq_administrator',
            'compliance_auditor',
            'executive_observer',
        ])) {
            return true;
        }

        return $user->primary_airport_id === $application->airport_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyStaffTitle([
            'system_administrator',
            'airport_manager',
            'shift_supervisor',
            'visa_processing_officer',
        ]);
    }

    public function transition(User $user, VisaApplication $application, string $action): bool
    {
        return app(WorkflowService::class)->canTransition($user, $application, $action);
    }
}
