<?php

namespace App\Livewire\Staff\BorderMovements;

use App\Models\AdmissibilityScreening;
use App\Models\Permit;
use App\Services\Border\AdmissibilityScreeningService;
use App\Services\Border\RecordBorderMovementService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    public string $permit_no = '';
    public string $movement_type = 'entry';
    public string $decision = 'admitted';
    public string $notes = '';
    public bool $supervisor_override_confirmed = false;
    public string $supervisor_override_reason = '';

    public ?int $permitId = null;
    public ?int $screeningId = null;
    public ?string $notice = null;
    public ?string $error = null;

    public function mount(): void
    {
        $permitNo = trim((string) request('permit_no'));

        if ($permitNo !== '') {
            $this->permit_no = $permitNo;
            $this->loadPermitByNumber();
        }
    }

    public function updatedMovementType(): void
    {
        $this->decision = $this->movement_type === 'exit' ? 'departed' : 'admitted';
        $this->screeningId = null;
        $this->notice = null;
        $this->supervisor_override_confirmed = false;
        $this->supervisor_override_reason = '';
    }

    public function searchPermit(): void
    {
        $this->reset([
            'permitId',
            'screeningId',
            'notice',
            'error',
            'supervisor_override_confirmed',
            'supervisor_override_reason',
        ]);

        $this->validate([
            'permit_no' => ['required', 'string', 'max:255'],
        ]);

        $this->loadPermitByNumber();
    }

    protected function loadPermitByNumber(): void
    {
        $permit = $this->permitQuery()
            ->where('permit_no', trim($this->permit_no))
            ->first();

        if (! $permit) {
            $this->error = 'No permit found for this permit number at your authorized location.';
            return;
        }

        $this->permitId = $permit->id;
        $this->notice = 'Permit loaded. Run admissibility screening before recording the movement.';
        $this->error = null;
    }

    public function runScreening(AdmissibilityScreeningService $screeningService): void
    {
        $permit = $this->selectedPermit();

        if (! $permit) {
            $this->searchPermit();
            $permit = $this->selectedPermit();
        }

        if (! $permit) {
            return;
        }

        $screening = $screeningService->screenPermit(
            $permit,
            Auth::user(),
            $this->movement_type,
            $this->notes !== '' ? $this->notes : null
        );

        $this->screeningId = $screening->id;
        $this->notice = 'Screening completed: ' . strtoupper($screening->status) . ' / ' . strtoupper($screening->risk_level);
        $this->error = null;
    }

    public function recordMovement(RecordBorderMovementService $movementService): void
    {
        $this->validate([
            'movement_type' => ['required', 'in:entry,exit'],
            'decision' => ['required', 'in:admitted,departed,refused,referred'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'supervisor_override_reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $permit = $this->selectedPermit();

        if (! $permit) {
            $this->error = 'Load a permit before recording a border movement.';
            return;
        }

        $screening = $this->screeningId
            ? AdmissibilityScreening::find($this->screeningId)
            : null;

        if (! $screening) {
            $this->error = 'Run admissibility screening before recording the movement.';
            return;
        }

        $requiresOverride = $this->requiresSupervisorOverride($screening);

        if ($requiresOverride && ! $this->supervisor_override_confirmed) {
            $this->error = 'This screening is not CLEAR. Confirm supervisor override before admitting or recording departure.';
            return;
        }

        if ($requiresOverride && strlen(trim($this->supervisor_override_reason)) < 20) {
            $this->error = 'Supervisor override reason must be at least 20 characters.';
            return;
        }

        $movement = $movementService->handle(
            $permit,
            Auth::user(),
            $this->movement_type,
            $this->decision,
            $screening,
            $this->notes !== '' ? $this->notes : null,
            $requiresOverride,
            $requiresOverride ? trim($this->supervisor_override_reason) : null
        );

        $this->notice = 'Border movement recorded: ' . $movement->movement_reference;
        $this->error = null;
        $this->screeningId = $movement->admissibility_screening_id;
    }

    public function render(): View
    {
        return view('livewire.staff.border-movements.create', [
            'permit' => $this->selectedPermit(),
            'screening' => $this->screeningId ? AdmissibilityScreening::find($this->screeningId) : null,
            'requiresSupervisorOverride' => $this->screeningId
                ? $this->requiresSupervisorOverride(AdmissibilityScreening::find($this->screeningId))
                : false,
        ]);
    }

    protected function requiresSupervisorOverride(?AdmissibilityScreening $screening): bool
    {
        if (! $screening) {
            return false;
        }

        return in_array($this->decision, ['admitted', 'departed'], true)
            && ! in_array($screening->status, ['clear'], true);
    }

    protected function selectedPermit(): ?Permit
    {
        if (! $this->permitId) {
            return null;
        }

        return $this->permitQuery()->whereKey($this->permitId)->first();
    }

    protected function permitQuery()
    {
        $user = Auth::user();

        $query = Permit::query()
            ->with([
                'visaApplication.passenger',
                'visaApplication.airport',
                'visaApplication.pointOfEntry',
                'fraudFlags',
                'borderMovements',
            ]);

        if (
            $user &&
            ! $user->hasStaffTitle('system_administrator') &&
            ! $user->hasStaffTitle('hq_administrator') &&
            $user->primaryAirport?->id
        ) {
            $query->whereHas('visaApplication', function ($query) use ($user) {
                $query->where('airport_id', $user->primaryAirport->id);
            });
        }

        return $query;
    }
}
