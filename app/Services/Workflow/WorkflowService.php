<?php

namespace App\Services\Workflow;

use App\Models\StaffTitleWorkflowTransition;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use RuntimeException;

class WorkflowService
{
    public function scopeVisibleApplications(Builder $query, User $user): Builder
    {
        if ($user->hasAnyStaffTitle([
            'system_administrator',
            'hq_administrator',
            'compliance_auditor',
            'executive_observer',
        ])) {
            return $query;
        }

        return $query->where('airport_id', $user->primary_airport_id);
    }

    public function availableTransitions(User $user, VisaApplication $application): Collection
    {
        $staffTitleIds = $user->staffTitles()->pluck('staff_titles.id');

        return StaffTitleWorkflowTransition::query()
            ->with('staffTitle')
            ->whereIn('staff_title_id', $staffTitleIds)
            ->where('from_status_key', $application->status->value)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function canTransition(User $user, VisaApplication $application, string $action): bool
    {
        return $this->availableTransitions($user, $application)
            ->contains(fn (StaffTitleWorkflowTransition $transition) => $transition->action === $action);
    }

    public function resolveTransition(User $user, VisaApplication $application, string $action): StaffTitleWorkflowTransition
    {
        $transition = $this->availableTransitions($user, $application)
            ->first(fn (StaffTitleWorkflowTransition $item) => $item->action === $action);

        if (! $transition) {
            throw new RuntimeException('Transition is not allowed for this user.');
        }

        return $transition;
    }

    public function permitCanBeIssued(VisaApplication $application): bool
    {
        $hasVerifiedPayment = $application->latestInvoice?->payments()
            ->where('status', 'successful')
            ->exists() ?? false;

        $hasApprovedWaiver = $application->latestWaiverApproval?->approved ?? false;

        return in_array($application->status->value, ['approved', 'permit_ready', 'permit_issued'], true)
            && ($hasVerifiedPayment || $hasApprovedWaiver || $application->is_fee_waived);
    }

    public function requiresChecker(VisaApplication $application): bool
    {
        return $application->requires_checker_approval || $application->is_fee_waived;
    }
}
