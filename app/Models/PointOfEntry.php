<?php

// FILE: app/Models/PointOfEntry.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PointOfEntry extends Model
{
    use HasFactory;

    protected $table = 'points_of_entry';

    protected $fillable = [
        'airport_id',
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

    public function visaApplications(): HasMany
    {
        return $this->hasMany(VisaApplication::class, 'point_of_entry_id');
    }
}