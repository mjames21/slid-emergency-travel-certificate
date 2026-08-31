<?php

namespace App\Livewire\Hq\EvisaApplications;

use App\Models\VisaApplication;
use App\Services\Evisa\ApproveOnlineEvisaApplicationService;
use App\Services\Evisa\RecordOnlineEvisaPaymentService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;
use Throwable;

class Index extends Component
{
    use WithPagination;

    public string $status = 'paid';

    public string $search = '';

    public string $receiptNumber = '';

    public string $paymentLookup = '';

    public string $paymentReceiptNumber = '';

    #[Locked]
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

    public function updatedReceiptNumber(): void
    {
        $this->resetErrorBag('receiptNumber');
        $this->error = null;
    }

    public function updatedPaymentLookup(): void
    {
        $this->resetErrorBag('paymentLookup');
        $this->error = null;
    }

    public function updatedPaymentReceiptNumber(): void
    {
        $this->resetErrorBag('paymentReceiptNumber');
        $this->error = null;
    }

    public function recordPaymentByReceipt(RecordOnlineEvisaPaymentService $service): void
    {
        $this->reset(['message', 'error']);
        $this->paymentLookup = trim($this->paymentLookup);
        $this->paymentReceiptNumber = trim($this->paymentReceiptNumber);

        $user = Auth::user();

        if (! ApproveOnlineEvisaApplicationService::canIssue($user)) {
            $this->error = 'Only an ETC Issuer can record payment and issue Emergency Travel Certificates.';

            return;
        }

        Validator::make([
            'paymentLookup' => $this->paymentLookup,
            'paymentReceiptNumber' => $this->paymentReceiptNumber,
        ], [
            'paymentLookup' => ['required', 'string', 'max:120'],
            'paymentReceiptNumber' => [
                'required',
                'string',
                'max:120',
                Rule::unique('payments', 'gateway_transaction_id'),
            ],
        ], [
            'paymentLookup.required' => 'Enter the ETC request, tracking, passport, or payment reference.',
            'paymentReceiptNumber.required' => 'Enter the WanGov/GovPay receipt number.',
            'paymentReceiptNumber.unique' => 'This receipt number has already been recorded.',
        ])->validate();

        $application = $this->applicationForPaymentLookup($this->paymentLookup);

        if (! $application) {
            throw ValidationException::withMessages([
                'paymentLookup' => 'No office ETC request was found for this reference.',
            ]);
        }

        if ($application->permit) {
            $this->message = 'Certificate has already been issued for this request.';

            return;
        }

        try {
            $payment = $service->handle($application, [
                'gateway' => 'wangov',
                'gateway_transaction_id' => $this->paymentReceiptNumber,
                'gateway_reference' => $application->latestInvoice?->payment_reference,
                'payment_channel' => 'office_receipt',
                'recorded_by' => $user?->id,
            ]);
        } catch (RuntimeException $exception) {
            $this->error = $exception->getMessage();

            return;
        } catch (Throwable $exception) {
            report($exception);
            $this->error = 'Payment could not be recorded. Check the request and receipt number, then try again.';

            return;
        }

        $this->paymentLookup = '';
        $this->paymentReceiptNumber = '';
        $this->status = 'paid';
        $this->resetPage();

        $this->message = 'Recorded payment receipt '.$payment->gateway_transaction_id.'. This request can now be issued.';
    }

    public function issueByReceipt(ApproveOnlineEvisaApplicationService $service): void
    {
        $this->reset(['message', 'error']);
        $this->receiptNumber = trim($this->receiptNumber);

        $user = Auth::user();

        if (! ApproveOnlineEvisaApplicationService::canIssue($user)) {
            $this->error = 'Only an ETC Issuer can approve and issue Emergency Travel Certificates.';

            return;
        }

        $this->validate([
            'receiptNumber' => ['required', 'string', 'max:120'],
        ], [
            'receiptNumber.required' => 'Enter the WanGov/GovPay receipt number.',
            'receiptNumber.max' => 'Receipt number is too long.',
        ]);

        $application = $this->applicationForReceipt($this->receiptNumber);

        if (! $application) {
            throw ValidationException::withMessages([
                'receiptNumber' => 'No Emergency Travel Certificate application was found for this receipt number.',
            ]);
        }

        if ($application->permit) {
            $this->message = 'Certificate has already been issued for receipt '.$this->receiptNumber.'.';

            return;
        }

        try {
            $permit = $service->handle($application, $user);
            $this->receiptNumber = '';
            $this->message = 'Generated Emergency Travel Certificate '.$permit->permit_no.' from the verified receipt.';
        } catch (RuntimeException $exception) {
            $this->error = $exception->getMessage();
        } catch (Throwable $exception) {
            report($exception);
            $this->error = 'The certificate could not be generated. Verify the payment and try again.';
        }
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
            $this->message = 'Approved and issued Emergency Travel Certificate '.$permit->permit_no.'. Email delivery was recorded separately.';
        } catch (RuntimeException $exception) {
            $this->error = $exception->getMessage();
        } catch (Throwable $exception) {
            report($exception);
            $this->error = 'The certificate could not be generated. Verify the payment and try again.';
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
                    ->orWhereHas('invoices', function (Builder $invoiceQuery) use ($search) {
                        $invoiceQuery->where('payment_reference', 'like', "%{$search}%")
                            ->orWhereHas('payments', function (Builder $paymentQuery) use ($search) {
                                $paymentQuery->where('gateway_transaction_id', 'like', "%{$search}%")
                                    ->orWhere('gateway_reference', 'like', "%{$search}%");
                            });
                    })
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

    private function applicationForReceipt(string $receiptNumber): ?VisaApplication
    {
        return VisaApplication::query()
            ->with(['passenger', 'latestInvoice.payments', 'permit'])
            ->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)
            ->whereHas('invoices', function (Builder $invoiceQuery) use ($receiptNumber) {
                $invoiceQuery->where('payment_reference', $receiptNumber)
                    ->orWhereHas('payments', function (Builder $paymentQuery) use ($receiptNumber) {
                        $paymentQuery->where('gateway_transaction_id', $receiptNumber)
                            ->orWhere('gateway_reference', $receiptNumber);
                    });
            })
            ->latest('submitted_at')
            ->latest('id')
            ->first();
    }

    private function applicationForPaymentLookup(string $lookup): ?VisaApplication
    {
        return VisaApplication::query()
            ->with(['passenger', 'latestInvoice.payments', 'permit'])
            ->where('application_channel', VisaApplication::CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE)
            ->where(function (Builder $query) use ($lookup) {
                $query->where('application_no', $lookup)
                    ->orWhere('public_tracking_code', $lookup)
                    ->orWhereHas('latestInvoice', function (Builder $invoiceQuery) use ($lookup) {
                        $invoiceQuery->where('payment_reference', $lookup);
                    })
                    ->orWhereHas('passenger', function (Builder $passengerQuery) use ($lookup) {
                        $passengerQuery->where('passport_number', $lookup);
                    });
            })
            ->latest('submitted_at')
            ->latest('id')
            ->first();
    }
}
