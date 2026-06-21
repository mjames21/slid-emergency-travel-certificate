<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FraudFlag extends Model
{
    protected $fillable = [
        'visa_application_id','permit_id','payment_id','flagged_by','flag_type',
        'severity','description','resolved','resolved_by','flagged_at','resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved' => 'boolean',
            'flagged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public function visaApplication(): BelongsTo { return $this->belongsTo(VisaApplication::class); }
    public function permit(): BelongsTo { return $this->belongsTo(Permit::class); }
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function flagger(): BelongsTo { return $this->belongsTo(User::class, 'flagged_by'); }
    public function resolver(): BelongsTo { return $this->belongsTo(User::class, 'resolved_by'); }
}
