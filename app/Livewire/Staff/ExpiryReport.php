<?php
// FILE: app/Livewire/Staff/ExpiryReport.php

namespace App\Livewire\Staff;

use App\Models\Permit;
use App\Support\PermitLifecycleStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class ExpiryReport extends Component
{
    use WithPagination;

    public string $scope = 'expiring_soon';
    public string $search = '';
    public string $start_date = '';
    public string $end_date = '';
    public int $perPage = 20;
    public string $sortField = 'valid_until';
    public string $sortDirection = 'asc';

    public function updatingScope(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStartDate(): void
    {
        $this->resetPage();
    }

    public function updatingEndDate(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        $allowed = ['permit_no', 'valid_until'];

        if (! in_array($field, $allowed, true)) {
            return;
        }

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
            return;
        }

        $this->sortField = $field;
        $this->sortDirection = 'asc';
    }

    public function render(): View
    {
        $baseQuery = $this->baseQuery();

        $search = trim($this->search);

        if ($search !== '') {
            $baseQuery->where(function (Builder $query) use ($search) {
                $query->where('permit_no', 'like', "%{$search}%")
                    ->orWhereHas('visaApplication.passenger', function (Builder $passengerQuery) use ($search) {
                        $passengerQuery
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%");
                    });
            });
        }

        $expiringSoonCount = (clone $baseQuery)
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
            ->count();

        $expiredCount = (clone $baseQuery)
            ->whereDate('valid_until', '<', today())
            ->count();

        $permits = $this->applyScopeFilters(clone $baseQuery)
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('permit_no')
            ->paginate($this->perPage);

        return view('livewire.staff.expiry-report', [
            'permits' => $permits,
            'expiringSoonCount' => $expiringSoonCount,
            'expiredCount' => $expiredCount,
            'dashboardAirportName' => Auth::user()?->primaryAirport?->name,
        ]);
    }

    protected function baseQuery(): Builder
    {
        $user = Auth::user();

        $query = Permit::query()
            ->with([
                'visaApplication.airport',
                'visaApplication.passenger',
                'issuer',
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

    protected function applyScopeFilters(Builder $query): Builder
    {
        if ($this->scope === 'expired_active') {
            $query->whereDate('valid_until', '<', today());

            if ($this->start_date !== '') {
                $query->whereDate('valid_until', '>=', $this->start_date);
            }

            if ($this->end_date !== '') {
                $query->whereDate('valid_until', '<=', $this->end_date);
            }

            return $query;
        }

        if ($this->start_date !== '' && $this->end_date !== '') {
            return $query
                ->whereDate('valid_until', '>=', $this->start_date)
                ->whereDate('valid_until', '<=', $this->end_date);
        }

        return $query
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays(7));
    }
}
