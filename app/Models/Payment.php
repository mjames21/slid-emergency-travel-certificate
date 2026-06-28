<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'confirmed_by', 'gateway', 'gateway_transaction_id', 'gateway_reference', 'payment_channel',
        'amount_due', 'amount_paid', 'currency', 'status', 'raw_payload', 'verification_payload',
        'initiated_at', 'paid_at', 'verified_at', 'failed_at', 'failure_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'status' => PaymentStatus::class,
            'raw_payload' => 'array',
            'verification_payload' => 'array',
            'initiated_at' => 'datetime',
            'paid_at' => 'datetime',
            'verified_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function permit(): HasOne
    {
        return $this->hasOne(Permit::class);
    }
}
