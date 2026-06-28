<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StaffTitle extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_staff_titles')
            ->withPivot(['assigned_by_user_id', 'assigned_at', 'is_primary'])
            ->withTimestamps();
    }
}
