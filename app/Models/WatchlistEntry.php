<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchlistEntry extends Model
{
    protected $fillable = [
        'created_by',
        'resolved_by',
        'source',
        'category',
        'severity',
        'status',
        'passport_number',
        'nationality_code',
        'surname',
        'given_names',
        'date_of_birth',
        'reason',
        'instructions',
        'listed_at',
        'expires_at',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'listed_at' => 'datetime',
            'expires_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
