<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PolicyApproval extends Model
{
    protected $fillable = [
        'requested_by',
        'approved_by',
        'policy_area',
        'standard_reference',
        'status',
        'version',
        'summary',
        'evidence',
        'requested_at',
        'approved_at',
        'expires_at',
        'decision_note',
    ];

    protected function casts(): array
    {
        return [
            'evidence' => 'array',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
