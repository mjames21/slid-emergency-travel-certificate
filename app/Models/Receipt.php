<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'payment_id',
        'issued_by',
        'receipt_source',
        'evidence_path',
        'evidence_original_name',
        'evidence_mime_type',
        'evidence_size',
        'evidence_hash',
        'notes',
        'document_path',
        'document_hash',
        'issued_at',
        'printed_at',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'printed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
    public function issuer(): BelongsTo { return $this->belongsTo(User::class, 'issued_by'); }
}
