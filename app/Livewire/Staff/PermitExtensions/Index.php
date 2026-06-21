<?php
// FILE: app/Livewire/Staff/PermitExtensions/Index.php

namespace App\Livewire\Staff\PermitExtensions;

use App\Models\PermitExtension;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = $this->baseQuery();

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('extension_no', 'like', "%{$search}%")
                    ->orWhereHas('originalPermit', function (Builder $permitQuery) use ($search) {
                        $permitQuery->where('permit_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('passenger', function (Builder $passengerQuery) use ($search) {
                        $passengerQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%");
                    });
            });
        }

        $extensions = $query
            ->latest('requested_at')
            ->latest('id')
            ->paginate($this->perPage);

        $statsBase = $this->baseQuery();

        return view('livewire.staff.permit-extensions.index', [
            'extensions' => $extensions,
            'stats' => [
                'total' => (clone $statsBase)->count(),
                'pending' => (clone $statsBase)->where('status', 'pending')->count(),
                'approved' => (clone $statsBase)->where('status', 'approved')->count(),
                'rejected' => (clone $statsBase)->where('status', 'rejected')->count(),
            ],
        ]);
    }

    protected function baseQuery(): Builder
    {
        $user = Auth::user();

        $query = PermitExtension::query()
            ->with([
                'originalPermit',
                'newPermit',
                'passenger',
                'requester',
                'approver',
                'rejector',
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
}