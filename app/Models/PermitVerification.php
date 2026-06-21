<?php

namespace App\Models;

use App\Enums\PermitVerificationResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermitVerification extends Model
{
    protected $fillable = ['permit_id','verification_code','result','channel','ip_address','user_agent','verified_at'];

    protected function casts(): array
    {
        return [
            'result' => PermitVerificationResult::class,
            'verified_at' => 'datetime',
        ];
    }

    public function permit(): BelongsTo
    {
        return $this->belongsTo(Permit::class);
    }
}
