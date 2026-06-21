<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorderMovement extends Model
{
    protected $fillable = [
        'admissibility_screening_id',
        'visa_application_id',
        'permit_id',
        'passenger_id',
        'airport_id',
        'point_of_entry_id',
        'officer_id',
        'movement_reference',
        'movement_type',
        'decision',
        'risk_level',
        'screening_status',
        'passport_number',
        'nationality_code',
        'carrier',
        'flight_number',
        'permit_valid_until',
        'overstay_days',
        'is_supervisor_override',
        'supervisor_override_reason',
        'occurred_at',
        'officer_notes',
        'decision_reasons',
    ];

    protected function casts(): array
    {
        return [
            'permit_valid_until' => 'date',
            'occurred_at' => 'datetime',
            'decision_reasons' => 'array',
            'overstay_days' => 'integer',
            'is_supervisor_override' => 'boolean',
        ];
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(AdmissibilityScreening::class, 'admissibility_screening_id');
    }

    public function visaApplication(): BelongsTo
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(Permit::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

    public function pointOfEntry(): BelongsTo
    {
        return $this->belongsTo(PointOfEntry::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }
}
