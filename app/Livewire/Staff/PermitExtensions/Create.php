<?php
// FILE: app/Livewire/Staff/PermitExtensions/Create.php

namespace App\Livewire\Staff\PermitExtensions;

use App\Models\Permit;
use App\Services\Passenger\BuildPassengerHistoryService;
use App\Services\Permit\CanExtendPermitService;
use App\Services\Permit\RequestPermitExtensionService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use RuntimeException;

class Create extends Component
{
    public string $permit_no = '';
    public ?Permit $permit = null;
    public string $eligibility_message = '';
    public bool $eligible = false;

    public string $requested_extra_days = '30';
    public string $current_valid_until = '';
    public string $requested_new_valid_until = '';
    public string $reason_code = '';
    public string $reason = '';
    public bool $is_fee_waived = false;
    public string $fee_amount = '0.00';
    public string $decision_note = '';

    public array $travelerHistory = [];

    public function updatedRequestedExtraDays($value): void
    {
        if (! $this->permit || ! $this->permit->valid_until) {
            $this->requested_new_valid_until = '';
            return;
        }

        $days = max((int) $value, 0);

        $this->requested_new_valid_until = Carbon::parse($this->permit->valid_until)
            ->addDays($days)
            ->toDateString();
    }

    public function searchPermit(
        CanExtendPermitService $canExtendPermitService,
        BuildPassengerHistoryService $historyService
    ): void {
        $this->validate([
            'permit_no' => ['required', 'string', 'max:255'],
        ]);

        $this->permit = Permit::query()
            ->with([
                'visaApplication.passenger',
                'issuer',
            ])
            ->where('permit_no', trim($this->permit_no))
            ->first();

        if (! $this->permit) {
            $this->eligible = false;
            $this->eligibility_message = 'Permit not found.';
            $this->current_valid_until = '';
            $this->requested_new_valid_until = '';
            $this->travelerHistory = [];
            return;
        }

        $eligibility = $canExtendPermitService->handle($this->permit);

        $this->eligible = $eligibility['allowed'];
        $this->eligibility_message = $eligibility['message'] ?? 'Permit is eligible for extension request.';
        $this->current_valid_until = optional($this->permit->valid_until)->toDateString() ?: '';
        $this->requested_new_valid_until = $this->permit->valid_until
            ? Carbon::parse($this->permit->valid_until)->addDays((int) $this->requested_extra_days)->toDateString()
            : '';

        $this->travelerHistory = $historyService->handle(
            $this->permit->visaApplication?->passenger?->passport_number
        );
    }

    public function submit(RequestPermitExtensionService $requestPermitExtensionService): void
    {
        $this->validate([
            'permit_no' => ['required', 'string', 'max:255'],
            'requested_extra_days' => ['required', 'integer', 'min:1', 'max:365'],
            'reason_code' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string', 'max:5000'],
            'is_fee_waived' => ['boolean'],
            'fee_amount' => ['nullable', 'numeric', 'min:0'],
            'decision_note' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! $this->permit) {
            $this->addError('permit_no', 'Search and select a permit first.');
            return;
        }

        if (! $this->eligible) {
            $this->addError('permit_no', $this->eligibility_message ?: 'This permit cannot be extended.');
            return;
        }

        try {
            $extension = $requestPermitExtensionService->handle(Auth::user(), $this->permit, [
                'requested_extra_days' => $this->requested_extra_days,
                'reason_code' => $this->reason_code,
                'reason' => $this->reason,
                'is_fee_waived' => $this->is_fee_waived,
                'fee_amount' => $this->fee_amount,
                'decision_note' => $this->decision_note,
            ]);

            session()->flash('success', 'Permit extension request created successfully.');

            $this->redirectRoute('staff.permit-extensions.show', $extension);
        } catch (RuntimeException $e) {
            $this->addError('permit_no', $e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.staff.permit-extensions.create');
    }
}