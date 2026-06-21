<?php
// FILE: app/Livewire/Hq/Dashboard.php

namespace App\Livewire\Hq;

use App\Models\Permit;
use App\Support\PermitLifecycleStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Dashboard extends Component
{
    public function render(): View
    {
        $permitBaseQuery = $this->permitsQuery();

        $expiringSoonPermitsCount = (clone $permitBaseQuery)
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
            ->count();

        $expiredPermitsCount = (clone $permitBaseQuery)
            ->whereDate('valid_until', '<', today())
            ->count();

        $expiringSoonPermits = (clone $permitBaseQuery)
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
            ->orderBy('valid_until')
            ->take(10)
            ->get();

        $expiredPermits = (clone $permitBaseQuery)
            ->whereDate('valid_until', '<', today())
            ->orderBy('valid_until')
            ->take(10)
            ->get();

        $expiringSoonByAirport = (clone $permitBaseQuery)
            ->whereDate('valid_until', '>=', today())
            ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
            ->get()
            ->groupBy(function (Permit $permit) {
                return $permit->visaApplication?->airport?->name ?: 'Unknown Airport';
            })
            ->map(fn ($items) => $items->count())
            ->sortDesc();

        $expiredByAirport = (clone $permitBaseQuery)
            ->whereDate('valid_until', '<', today())
            ->get()
            ->groupBy(function (Permit $permit) {
                return $permit->visaApplication?->airport?->name ?: 'Unknown Airport';
            })
            ->map(fn ($items) => $items->count())
            ->sortDesc();

        return view('livewire.hq.dashboard', [
            'expiringSoonPermitsCount' => $expiringSoonPermitsCount,
            'expiredPermitsCount' => $expiredPermitsCount,
            'expiringSoonPermits' => $expiringSoonPermits,
            'expiredPermits' => $expiredPermits,
            'expiringSoonByAirport' => $expiringSoonByAirport,
            'expiredByAirport' => $expiredByAirport,
        ]);
    }

    protected function permitsQuery(): Builder
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
}
