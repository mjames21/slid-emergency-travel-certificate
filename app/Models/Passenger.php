<?php
// FILE: app/Models/Passenger.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Passenger extends Model
{
    use HasFactory;

    protected $fillable = [
        'surname',
        'given_names',
        'full_name',
        'nationality',
        'nationality_code',
        'passport_number',
        'passport_expiry_date',
        'sex',
        'date_of_birth',
        'country_of_birth',
        'country_of_residence',
        'occupation',
        'email',
        'phone',
        'passport_biodata_image_path',
        'passport_mrz_image_path',
        'passport_mrz_raw',
        'passport_mrz_data',
        'passport_mrz_confidence',
        'passport_mrz_extracted_at',
        'passport_mrz_extracted_by',
        'passport_biodata_captured_at',
        'passport_biodata_captured_by',
        'passport_biodata_capture_device',
    ];

    protected function casts(): array
    {
        return [
            'passport_expiry_date' => 'date',
            'date_of_birth' => 'date',
            'passport_mrz_data' => 'array',
            'passport_mrz_confidence' => 'decimal:2',
            'passport_mrz_extracted_at' => 'datetime',
            'passport_biodata_captured_at' => 'datetime',
        ];
    }

    public function passportBiodataCapturedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'passport_biodata_captured_by');
    }

    public function visaApplications(): HasMany
    {
        return $this->hasMany(VisaApplication::class);
    }

    public function admissibilityScreenings(): HasMany
    {
        return $this->hasMany(AdmissibilityScreening::class);
    }

    public function borderMovements(): HasMany
    {
        return $this->hasMany(BorderMovement::class);
    }
}
