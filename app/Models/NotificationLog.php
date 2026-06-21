<?php

namespace App\Models;

use App\Enums\NotificationChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    protected $fillable = [
        'permit_id','visa_application_id','channel','recipient','subject','status',
        'provider_message_id','payload','sent_at','failed_at','failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'payload' => 'array',
            'sent_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function permit(): BelongsTo { return $this->belongsTo(Permit::class); }
    public function visaApplication(): BelongsTo { return $this->belongsTo(VisaApplication::class); }
}
