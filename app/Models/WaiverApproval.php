<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaiverApproval extends Model
{
    protected $fillable = ['visa_application_id','requested_by','approved_by','reason_category','reason','authority_reference','approved','requested_at','approved_at'];

    protected function casts(): array
    {
        return [
            'approved' => 'boolean',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function visaApplication(): BelongsTo { return $this->belongsTo(VisaApplication::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
