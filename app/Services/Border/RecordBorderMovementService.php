<?php

namespace App\Services\Border;

use App\Models\AdmissibilityScreening;
use App\Models\BorderMovement;
use App\Models\Permit;
use App\Models\User;
use App\Services\Audit\WriteAuditLogService;
use Illuminate\Support\Str;

class RecordBorderMovementService
{
    public function __construct(
        protected AdmissibilityScreeningService $screeningService,
        protected WriteAuditLogService $writeAuditLogService
    ) {
    }

    public function handle(
        Permit $permit,
        User $officer,
        string $movementType,
        string $decision,
        ?AdmissibilityScreening $screening = null,
        ?string $notes = null,
        bool $isSupervisorOverride = false,
        ?string $supervisorOverrideReason = null
    ): BorderMovement {
        $permit->loadMissing(['visaApplication.passenger', 'visaApplication.pointOfEntry']);

        $application = $permit->visaApplication;
        $passenger = $application?->passenger;

        if (! $application || ! $passenger) {
            throw new \RuntimeException('Permit is missing traveler or application context.');
        }

        $screening ??= $this->screeningService->screenPermit($permit, $officer, $movementType, $notes);

        $overstayDays = 0;

        if ($movementType === 'exit' && $permit->valid_until && today()->greaterThan($permit->valid_until)) {
            $overstayDays = $permit->valid_until->diffInDays(today());
        }

        $movement = BorderMovement::create([
            'admissibility_screening_id' => $screening->id,
            'visa_application_id' => $application->id,
            'permit_id' => $permit->id,
            'passenger_id' => $passenger->id,
            'airport_id' => $application->airport_id,
            'point_of_entry_id' => $application->point_of_entry_id,
            'officer_id' => $officer->id,
            'movement_reference' => $this->reference('MOV'),
            'movement_type' => $movementType,
            'decision' => $decision,
            'risk_level' => $screening->risk_level,
            'screening_status' => $screening->status,
            'passport_number' => $passenger->passport_number,
            'nationality_code' => $passenger->nationality_code,
            'carrier' => $application->flight_carrier,
            'flight_number' => $application->flight_number,
            'permit_valid_until' => $permit->valid_until,
            'overstay_days' => $overstayDays,
            'is_supervisor_override' => $isSupervisorOverride,
            'supervisor_override_reason' => $supervisorOverrideReason,
            'occurred_at' => now(),
            'officer_notes' => $notes,
            'decision_reasons' => $screening->reasons,
        ]);

        $this->writeAuditLogService->handle(
            $officer,
            'border.movement_recorded',
            $movement,
            [
                'movement_reference' => $movement->movement_reference,
                'permit_no' => $permit->permit_no,
                'passport_number' => $passenger->passport_number,
                'movement_type' => $movementType,
                'decision' => $decision,
                'is_supervisor_override' => $isSupervisorOverride,
            ],
            'Border movement recorded'
        );

        return $movement;
    }

    protected function reference(string $prefix): string
    {
        return $prefix . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
    }
}
