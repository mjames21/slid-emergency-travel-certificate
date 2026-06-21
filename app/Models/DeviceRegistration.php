<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceRegistration extends Model
{
    protected $fillable = [
        'airport_id','desk_id','device_name','device_identifier','hostname',
        'printer_name','ip_address','trusted','active','registered_by','registered_at','last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'trusted' => 'boolean',
            'active' => 'boolean',
            'registered_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function airport(): BelongsTo { return $this->belongsTo(Airport::class); }
    public function desk(): BelongsTo { return $this->belongsTo(Desk::class); }
    public function registrar(): BelongsTo { return $this->belongsTo(User::class, 'registered_by'); }
    public function permitPrintLogs(): HasMany { return $this->hasMany(PermitPrintLog::class); }
}
