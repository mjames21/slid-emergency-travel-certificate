<?php

namespace App\Livewire\Hq\EvisaApplications;

use App\Models\VisaApplication;
use App\Services\Evisa\ApproveOnlineEvisaApplicationService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $status = 'paid';

    public string $search = '';

    public int $perPage = 20;

    public ?string $message = null;

    public ?string $error = null;

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function issue(int $applicationId, ApproveOnlineEvisaApplicationService $service): void
    {
        $this->reset(['message', 'error']);

        $user = Auth::user();

        if (! ApproveOnlineEvisaApplicationService::canIssue($user)) {
            $this->error = 'Only an ETC Issuer can approve and issue Emergency Travel Certificates.';

            return;
        }

        $application = VisaApplication::query()
            ->with(['passenger', 'latestInvoice.payments', 'permit'])
            ->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)
            ->findOrFail($applicationId);

        if ($application->permit) {
            $this->message = 'Certificate has already been issued for this application.';

            return;
        }

        try {
            $permit = $service->handle($application, $user);
            $this->message = 'Approved, issued, and emailed Emergency Travel Certificate '.$permit->permit_no.' to the traveler.';
        } catch (\Throwable $e) {
            report($e);
            $this->error = $e->getMessage();
        }
    }

    public function render(): View
    {
        $query = VisaApplication::query()
            ->with(['passenger', 'latestInvoice.payments', 'permit'])
            ->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)
            ->latest('submitted_at')
            ->latest('id');

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        $search = trim($this->search);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('application_no', 'like', "%{$search}%")
                    ->orWhere('public_tracking_code', 'like', "%{$search}%")
                    ->orWhereHas('passenger', function (Builder $passengerQuery) use ($search) {
                        $passengerQuery->where('full_name', 'like', "%{$search}%")
                            ->orWhere('passport_number', 'like', "%{$search}%");
                    });
            });
        }

        return view('livewire.hq.evisa-applications.index', [
            'applications' => $query->paginate($this->perPage),
            'canIssueEtc' => ApproveOnlineEvisaApplicationService::canIssue(Auth::user()),
            'paidCount' => VisaApplication::query()->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)->where('status', 'paid')->count(),
            'awaitingPaymentCount' => VisaApplication::query()->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)->where('status', 'awaiting_payment')->count(),
            'issuedCount' => VisaApplication::query()->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)->where('status', 'permit_issued')->count(),
        ]);
    }
}
