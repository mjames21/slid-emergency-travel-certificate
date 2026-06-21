<?php
// FILE: app/Models/PermitExtension.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitExtension extends Model
{
    protected $fillable = [
        'original_permit_id',
        'new_permit_id',
        'visa_application_id',
        'passenger_id',
        'extension_no',
        'requested_extra_days',
        'current_valid_until',
        'requested_new_valid_until',
        'reason_code',
        'reason',
        'is_fee_waived',
        'fee_amount',
        'status',
        'requested_at',
        'approved_at',
        'rejected_at',
        'requested_by',
        'approved_by',
        'rejected_by',
        'decision_note',
    ];

    protected $casts = [
        'current_valid_until' => 'date',
        'requested_new_valid_until' => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'is_fee_waived' => 'boolean',
        'fee_amount' => 'decimal:2',
    ];

    public function originalPermit(): BelongsTo
    {
        return $this->belongsTo(Permit::class, 'original_permit_id');
    }

    public function newPermit(): BelongsTo
    {
        return $this->belongsTo(Permit::class, 'new_permit_id');
    }

    public function visaApplication(): BelongsTo
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function passenger(): BelongsTo
    {
        return $this->belongsTo(Passenger::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
}