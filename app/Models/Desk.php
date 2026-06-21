<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Desk extends Model
{
    use HasFactory;

    protected $fillable = ['airport_id','name','code','location','description','active'];

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }

    public function airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

    public function visaApplications(): HasMany
    {
        return $this->hasMany(VisaApplication::class);
    }

    public function deviceRegistrations(): HasMany
    {
        return $this->hasMany(DeviceRegistration::class);
    }
}
