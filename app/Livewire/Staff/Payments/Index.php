<?php

namespace App\Livewire\Staff\Payments;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\VisaApplication;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = 'open';
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
        return view('livewire.staff.payments.index', [
            'invoices' => $this->query()->paginate($this->perPage),
            'paymentAirportName' => Auth::user()?->primaryAirport?->name,
        ]);
    }

    protected function query(): Builder
    {
        $user = Auth::user();
        $search = trim($this->search);

        $query = Invoice::query()
            ->with([
                'visaApplication.passenger',
                'visaApplication.airport',
                'payments.receipt',
            ])
            ->whereHas('visaApplication', function (Builder $query) use ($user) {
                $query->where(function (Builder $query) {
                    $query->whereNull('visa_type')
                        ->orWhere('visa_type', '!=', VisaApplication::TYPE_EMERGENCY_TRAVEL_CERTIFICATE);
                });

                if (
                    $user &&
                    ! $user->hasStaffTitle('system_administrator') &&
                    $user->primary_airport_id
                ) {
                    $query->where('airport_id', $user->primary_airport_id);
                }
            });

        match ($this->status) {
            'paid' => $query->where('status', InvoiceStatus::Paid),
            'pending' => $query->where('status', InvoiceStatus::Pending),
            'initiated' => $query->where('status', InvoiceStatus::Initiated),
            'all' => null,
            default => $query->whereIn('status', [
                InvoiceStatus::Pending,
                InvoiceStatus::Initiated,
            ]),
        };

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('invoice_no', 'like', "%{$search}%")
                    ->orWhere('payment_reference', 'like', "%{$search}%")
                    ->orWhereHas('visaApplication', function (Builder $applicationQuery) use ($search) {
                        $applicationQuery->where('application_no', 'like', "%{$search}%");
                    })
                    ->orWhereHas('visaApplication.passenger', function (Builder $passengerQuery) use ($search) {
                        $passengerQuery
                            ->where('passport_number', 'like', "%{$search}%")
                            ->orWhere('full_name', 'like', "%{$search}%")
                            ->orWhere('surname', 'like', "%{$search}%")
                            ->orWhere('given_names', 'like', "%{$search}%");
                    })
                    ->orWhereHas('payments.receipt', function (Builder $receiptQuery) use ($search) {
                        $receiptQuery->where('receipt_no', 'like', "%{$search}%");
                    });
            });
        }

        return $query
            ->latest('issued_at')
            ->latest('id');
    }
}
