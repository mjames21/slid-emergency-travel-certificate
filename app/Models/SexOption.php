<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SexOption extends Model
{
    use HasFactory;

    protected $table = 'sex_options';

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'label',
        'value',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getLabelAttribute(): string
    {
        return (string) $this->name;
    }

    public function getValueAttribute(): string
    {
        return (string) $this->code;
    }
}