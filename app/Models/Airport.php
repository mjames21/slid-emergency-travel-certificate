<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = ['name','code','city','country','timezone','active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function desks(): HasMany
    {
        return $this->hasMany(Desk::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['desk_id', 'is_primary', 'assigned_at', 'assigned_by_user_id'])
            ->withTimestamps();
    }

    public function visaApplications(): HasMany
    {
        return $this->hasMany(VisaApplication::class);
    }

    public function permits(): HasMany
    {
        return $this->hasManyThrough(Permit::class, VisaApplication::class);
    }
    public function pointsOfEntry(): HasMany
{
    return $this->hasMany(PointOfEntry::class);
}
}
