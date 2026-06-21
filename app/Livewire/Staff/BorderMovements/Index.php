<?php

namespace App\Livewire\Staff\BorderMovements;

use App\Models\BorderMovement;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $movement_type = '';
    public string $decision = '';
    public int $perPage = 20;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingMovementType(): void
    {
        $this->resetPage();
    }

    public function updatingDecision(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $query = $this->baseQuery();

        if ($this->movement_type !== '') {
            $query->where('movement_type', $this->movement_type);
        }

        if ($this->decision !== '') {
            $query->where('decision', $this->decision);
        }

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('movement_reference', 'like', "%{$search}%")
                    ->orWhere('passport_number', 'like', "%{$search}%")
                    ->orWhereHas('permit', fn (Builder $permitQuery) => $permitQuery->where('permit_no', 'like', "%{$search}%"))
                    ->orWhereHas('passenger', fn (Builder $passengerQuery) => $passengerQuery->where('full_name', 'like', "%{$search}%"));
            });
        }

        return view('livewire.staff.border-movements.index', [
            'movements' => $query->paginate($this->perPage),
            'entriesTodayCount' => (clone $this->baseQuery())->where('movement_type', 'entry')->whereDate('occurred_at', today())->count(),
            'exitsTodayCount' => (clone $this->baseQuery())->where('movement_type', 'exit')->whereDate('occurred_at', today())->count(),
            'referralsTodayCount' => (clone $this->baseQuery())->whereIn('decision', ['refused', 'referred'])->whereDate('occurred_at', today())->count(),
            'overridesTodayCount' => (clone $this->baseQuery())->where('is_supervisor_override', true)->whereDate('occurred_at', today())->count(),
        ]);
    }

    protected function baseQuery(): Builder
    {
        $user = Auth::user();

        $query = BorderMovement::query()
            ->with([
                'permit',
                'passenger',
                'airport',
                'officer',
                'screening',
            ])
            ->latest('occurred_at')
            ->latest('id');

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
