<?php

// FILE: app/Livewire/Staff/Dashboard.php

namespace App\Livewire\Staff;

use App\Models\Permit;
use App\Models\PermitExtension;
use App\Models\BorderMovement;
use App\Support\PermitLifecycleStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function mount(): void
    {
        $user = Auth::user();

        if (
            $user &&
            $user->hasStaffTitle('payment_officer') &&
            ! $user->hasAnyStaffTitle([
                'system_administrator',
                'airport_manager',
                'shift_supervisor',
                'visa_processing_officer',
            ])
        ) {
            $this->redirectRoute('staff.payments.index');
        }
    }

    public function render(): View
    {
        $user = Auth::user();

        $extensionBaseQuery = $this->permitExtensionsQuery();

        $pendingExtensionApprovalsCount = (clone $extensionBaseQuery)
            ->where('status', 'pending')
            ->count();

        $extensionsTodayCount = (clone $extensionBaseQuery)
            ->whereDate('created_at', today())
            ->count();

        $approvedExtensionsTodayCount = (clone $extensionBaseQuery)
            ->where('status', 'approved')
            ->whereDate('approved_at', today())
            ->count();

        $rejectedExtensionsTodayCount = (clone $extensionBaseQuery)
            ->where('status', 'rejected')
            ->whereDate('rejected_at', today())
            ->count();

        $feeWaivedExtensionsTodayCount = (clone $extensionBaseQuery)
            ->where('is_fee_waived', true)
            ->whereDate('created_at', today())
            ->count();

        $recentPendingExtensions = (clone $extensionBaseQuery)
            ->where('status', 'pending')
            ->latest('requested_at')
            ->latest('id')
            ->take(5)
            ->get();

        $permitBaseQuery = $this->permitsQuery();
        $movementBaseQuery = $this->borderMovementsQuery();

        $expiringSoonPermitsCount = (clone $permitBaseQuery)
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
            ->count();

        $expiredPermitsCount = (clone $permitBaseQuery)
            ->whereDate('valid_until', '<', today())
            ->count();

        $entriesTodayCount = (clone $movementBaseQuery)
            ->where('movement_type', 'entry')
            ->whereDate('occurred_at', today())
            ->count();

        $exitsTodayCount = (clone $movementBaseQuery)
            ->where('movement_type', 'exit')
            ->whereDate('occurred_at', today())
            ->count();

        $referralsTodayCount = (clone $movementBaseQuery)
            ->whereIn('decision', ['refused', 'referred'])
            ->whereDate('occurred_at', today())
            ->count();

        $recentBorderMovements = (clone $movementBaseQuery)
            ->latest('occurred_at')
            ->latest('id')
            ->take(5)
            ->get();

        $canReviewExtensions = $user && (
            $user->hasStaffTitle('system_administrator') ||
            $user->hasStaffTitle('airport_manager') ||
            $user->hasStaffTitle('shift_supervisor')
        );

        return view('livewire.staff.dashboard', [
            'pendingExtensionApprovalsCount' => $pendingExtensionApprovalsCount,
            'extensionsTodayCount' => $extensionsTodayCount,
            'approvedExtensionsTodayCount' => $approvedExtensionsTodayCount,
            'rejectedExtensionsTodayCount' => $rejectedExtensionsTodayCount,
            'feeWaivedExtensionsTodayCount' => $feeWaivedExtensionsTodayCount,
            'recentPendingExtensions' => $recentPendingExtensions,
            'expiringSoonPermitsCount' => $expiringSoonPermitsCount,
            'expiredPermitsCount' => $expiredPermitsCount,
            'entriesTodayCount' => $entriesTodayCount,
            'exitsTodayCount' => $exitsTodayCount,
            'referralsTodayCount' => $referralsTodayCount,
            'recentBorderMovements' => $recentBorderMovements,
            'canReviewExtensions' => $canReviewExtensions,
            'dashboardAirportName' => $user?->primaryAirport?->name,
        ]);
    }

    protected function permitExtensionsQuery(): Builder
    {
        $user = Auth::user();

        $query = PermitExtension::query()
            ->with([
                'originalPermit',
                'passenger',
                'requester',
                'visaApplication.airport',
            ]);

        if (
            $user &&
            ! $user->hasStaffTitle('system_administrator') &&
            $user->primaryAirport?->id
        ) {
            $query->whereHas('visaApplication', function (Builder $query) use ($user) {
                $query->where('airport_id', $user->primaryAirport->id);
            });
        }

        return $query;
    }

    protected function permitsQuery(): Builder
    {
        $user = Auth::user();

        $query = Permit::query()
            ->with([
                'visaApplication.airport',
                'visaApplication.passenger',
            ])
            ->whereNotNull('valid_until');

        PermitLifecycleStatus::constrainActive($query);

        if (
            $user &&
            ! $user->hasStaffTitle('system_administrator') &&
            $user->primaryAirport?->id
        ) {
            $query->whereHas('visaApplication', function (Builder $query) use ($user) {
                $query->where('airport_id', $user->primaryAirport->id);
            });
        }

        return $query;
    }

    protected function borderMovementsQuery(): Builder
    {
        $user = Auth::user();

        $query = BorderMovement::query()
            ->with([
                'permit',
                'passenger',
                'airport',
                'officer',
            ]);

        if (
            $user &&
            ! $user->hasStaffTitle('system_administrator') &&
            ! $user->hasStaffTitle('hq_administrator') &&
            $user->primaryAirport?->id
        ) {
            $query->where('airport_id', $user->primaryAirport->id);
        }

        return $query;
    }
}
