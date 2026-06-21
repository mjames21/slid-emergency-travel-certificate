<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitPrintLog extends Model
{
    protected $fillable = [
        'permit_id','printed_by','airport_id','desk_id','device_registration_id',
        'terminal_name','printer_name','is_reprint','reason_code','reason','printed_at',
    ];

    protected function casts(): array
    {
        return [
            'is_reprint' => 'boolean',
            'printed_at' => 'datetime',
        ];
    }

    public function permit(): BelongsTo { return $this->belongsTo(Permit::class); }
    public function printer(): BelongsTo { return $this->belongsTo(User::class, 'printed_by'); }
    public function airport(): BelongsTo { return $this->belongsTo(Airport::class); }
    public function desk(): BelongsTo { return $this->belongsTo(Desk::class); }
    public function deviceRegistration(): BelongsTo { return $this->belongsTo(DeviceRegistration::class); }
}
