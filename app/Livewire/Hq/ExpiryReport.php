<?php
// FILE: app/Livewire/Hq/ExpiryReport.php

namespace App\Livewire\Hq;

use App\Models\Airport;
use App\Models\Permit;
use App\Support\PermitLifecycleStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class ExpiryReport extends Component
{
    use WithPagination;

    public string $scope = 'expiring_soon';
    public string $search = '';
    public string $airport_id = '';
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

    public function updatingAirportId(): void
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

        if ($this->airport_id !== '') {
            $baseQuery->whereHas('visaApplication', function (Builder $query) {
                $query->where('airport_id', $this->airport_id);
            });
        }

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

        return view('livewire.hq.expiry-report', [
            'permits' => $permits,
            'airports' => Airport::query()->orderBy('name')->get(['id', 'name', 'code']),
            'expiringSoonCount' => $expiringSoonCount,
            'expiredCount' => $expiredCount,
        ]);
    }

    protected function baseQuery(): Builder
    {
        return Permit::query()
            ->with([
                'visaApplication.airport',
                'visaApplication.passenger',
                'issuer',
            ])
            ->whereNotNull('valid_until')
            ->tap(fn (Builder $query) => PermitLifecycleStatus::constrainActive($query));
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
