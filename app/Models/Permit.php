<?php

namespace App\Models;

use App\Enums\PermitStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Permit extends Model
{
    use HasFactory;

    protected $fillable = [
        'permit_no',
        'visa_application_id',
        'payment_id',
        'issued_by',
        'permit_type',
        'status',
        'issued_at',
        'valid_from',
        'valid_until',
        'verification_code',
        'security_seal',
        'seal_algorithm',
        'seal_version',
        'qr_code_path',
        'document_path',
        'document_hash',
        'virtual_payload_hash',
        'mrz_type',
        'mrz_line_1',
        'mrz_line_2',
        'print_count',
        'last_printed_at',
        'is_virtual_available',
        'is_duplicate_print',
        'cancelled_at',
        'revoked_at',
        'revocation_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => PermitStatus::class,
            'issued_at' => 'datetime',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'print_count' => 'integer',
            'last_printed_at' => 'datetime',
            'is_virtual_available' => 'boolean',
            'is_duplicate_print' => 'boolean',
            'cancelled_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function visaApplication(): BelongsTo
    {
        return $this->belongsTo(VisaApplication::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(PermitVerification::class);
    }

    public function printLogs(): HasMany
    {
        return $this->hasMany(PermitPrintLog::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
