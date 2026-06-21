<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TravelDocumentAlert extends Model
{
    protected $fillable = [
        'created_by',
        'source',
        'document_type',
        'document_status',
        'document_number',
        'issuing_state',
        'date_of_birth',
        'holder_name',
        'reason',
        'reported_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'reported_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
