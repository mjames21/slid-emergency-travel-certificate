<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissibilityScreening extends Model
{
    protected $fillable = [
        'visa_application_id',
        'permit_id',
        'passenger_id',
        'airport_id',
        'screened_by',
        'screening_reference',
        'movement_type',
        'status',
        'risk_level',
        'passport_valid',
        'permit_valid',
        'mrz_verified',
        'traveler_history_reviewed',
        'watchlist_checked',
        'carrier_document_check',
        'protection_referral_required',
        'reasons',
        'recommendations',
        'officer_notes',
        'screened_at',
    ];

    protected function casts(): array
    {
        return [
            'passport_valid' => 'boolean',
            'permit_valid' => 'boolean',
            'mrz_verified' => 'boolean',
            'traveler_history_reviewed' => 'boolean',
            'watchlist_checked' => 'boolean',
            'carrier_document_check' => 'boolean',
            'protection_referral_required' => 'boolean',
            'reasons' => 'array',
            'recommendations' => 'array',
            'screened_at' => 'datetime',
        ];
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

    public function screener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'screened_by');
    }
}
