<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelRequirementRule extends Model
{
    protected $fillable = [
        'created_by',
        'source',
        'nationality_code',
        'document_type',
        'visa_type',
        'purpose_of_visit',
        'carrier_code',
        'max_stay_days',
        'min_passport_validity_days',
        'visa_required',
        'return_ticket_required',
        'host_address_required',
        'active',
        'effective_from',
        'effective_until',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'max_stay_days' => 'integer',
            'min_passport_validity_days' => 'integer',
            'visa_required' => 'boolean',
            'return_ticket_required' => 'boolean',
            'host_address_required' => 'boolean',
            'active' => 'boolean',
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
