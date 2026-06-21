<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_no','visa_application_id','created_by','amount','currency','payment_reference','gateway',
        'status','issued_at','expires_at','paid_at','cancelled_at','cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => InvoiceStatus::class,
            'issued_at' => 'datetime',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function visaApplication(): BelongsTo { return $this->belongsTo(VisaApplication::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
}
