<?php
// FILE: app/Livewire/Staff/PermitExtensions/Show.php

namespace App\Livewire\Staff\PermitExtensions;

use App\Models\PermitExtension;
use App\Services\Permit\ApprovePermitExtensionService;
use App\Services\Permit\RejectPermitExtensionService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use RuntimeException;

class Show extends Component
{
    public PermitExtension $permitExtension;
    public string $decision_note = '';

    public function mount(PermitExtension $permitExtension): void
    {
        $this->permitExtension = $permitExtension->load([
            'originalPermit',
            'newPermit',
            'visaApplication',
            'passenger',
            'requester',
            'approver',
            'rejector',
        ]);

        $this->decision_note = $this->permitExtension->decision_note ?? '';
    }

    public function approve(ApprovePermitExtensionService $service): void
    {
        $this->ensureCanReview();

        try {
            $this->permitExtension = $service->handle(
                Auth::user(),
                $this->permitExtension,
                $this->decision_note
            );

            session()->flash('success', 'Permit extension approved successfully.');
        } catch (RuntimeException $e) {
            $this->addError('decision_note', $e->getMessage());
        }
    }

    public function reject(RejectPermitExtensionService $service): void
    {
        $this->ensureCanReview();

        try {
            $this->permitExtension = $service->handle(
                Auth::user(),
                $this->permitExtension,
                $this->decision_note
            );

            session()->flash('success', 'Permit extension rejected successfully.');
        } catch (RuntimeException $e) {
            $this->addError('decision_note', $e->getMessage());
        }
    }

    protected function ensureCanReview(): void
    {
        $user = Auth::user();

        $allowed = $user && (
            $user->hasStaffTitle('system_administrator') ||
            $user->hasStaffTitle('airport_manager') ||
            $user->hasStaffTitle('shift_supervisor')
        );

        abort_unless($allowed, 403);
    }

    public function render(): View
    {
        $permitExtension = $this->permitExtension->fresh([
            'originalPermit',
            'newPermit',
            'visaApplication',
            'passenger',
            'requester',
            'approver',
            'rejector',
        ]);

        return view('livewire.staff.permit-extensions.show', [
            'permitExtension' => $permitExtension,
            'canReview' => Auth::user() && (
                Auth::user()->hasStaffTitle('system_administrator') ||
                Auth::user()->hasStaffTitle('airport_manager') ||
                Auth::user()->hasStaffTitle('shift_supervisor')
            ),
        ]);
    }
}