<?php
// FILE: app/Livewire/Staff/Applications/Index.php

namespace App\Livewire\Staff\Applications;

use App\Models\VisaApplication;
use App\Services\Passenger\BuildPassengerHistoryService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(BuildPassengerHistoryService $historyService): View
    {
        $query = $this->baseQuery();
        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                if (ctype_digit($search)) {
                    $query->orWhere('id', (int) $search);
                }

                $query->orWhere('purpose_of_visit', 'like', "%{$search}%")
                    ->orWhere('flight_number', 'like', "%{$search}%")
                    ->orWhereHas('passenger', function (Builder $passengerQuery) use ($search) {
                        $passengerQuery
                            ->where('passport_number', 'like', "%{$search}%")
                            ->orWhere('surname', 'like', "%{$search}%")
                            ->orWhere('given_names', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('airport', function (Builder $airportQuery) use ($search) {
                        $airportQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('desk', function (Builder $deskQuery) use ($search) {
                        $deskQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('permit', function (Builder $permitQuery) use ($search) {
                        $permitQuery->where('permit_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoices.payments.receipt', function (Builder $receiptQuery) use ($search) {
                        $receiptQuery
                            ->where('receipt_no', 'like', "%{$search}%")
                            ->orWhere('id', ctype_digit($search) ? (int) $search : 0);
                    });
            });
        }

        $applications = $query
            ->latest('created_at')
            ->latest('id')
            ->paginate($this->perPage);

        $travelerHistories = $this->buildTravelerHistories(
            collect($applications->items()),
            $historyService
        );

        return view('livewire.staff.applications.index', [
            'applications' => $applications,
            'travelerHistories' => $travelerHistories,
        ]);
    }

    protected function baseQuery(): Builder
    {
        $user = Auth::user();

        $query = VisaApplication::query()
            ->with([
                'passenger',
                'airport',
                'desk',
                'permit',
                'payment.receipt',
                'pointOfEntry',
            ]);

        if (
            $user &&
            ! $user->hasStaffTitle('system_administrator') &&
            $user->primaryAirport?->id
        ) {
            $query->where('airport_id', $user->primaryAirport->id);
        }

        return $query;
    }

    protected function buildTravelerHistories(
        Collection $applications,
        BuildPassengerHistoryService $historyService
    ): array {
        return $applications
            ->map(fn (VisaApplication $application) => $application->passenger?->passport_number)
            ->filter()
            ->map(fn ($passport) => strtoupper(trim((string) $passport)))
            ->unique()
            ->mapWithKeys(fn ($passport) => [
                $passport => $historyService->handle($passport),
            ])
            ->all();
    }
}
