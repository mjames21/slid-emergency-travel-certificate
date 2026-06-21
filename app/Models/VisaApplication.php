<?php

namespace App\Models;

use App\Enums\VisaApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class VisaApplication extends Model
{
    use HasFactory;

    public const TYPE_EMERGENCY_TRAVEL_CERTIFICATE = 'emergency_travel_certificate';
    public const CHANNEL_ONLINE_EMERGENCY_TRAVEL_CERTIFICATE = 'online_emergency_travel_certificate';

    protected $fillable = [
        'application_no','passenger_id','airport_id','desk_id','created_by','submitted_by','approved_by','reviewed_by',
        'visa_type','application_channel','applicant_category','regional_category','identity_document_type','identity_document_number',
        'place_of_birth','marital_status','applicant_address','public_tracking_code','public_access_token','status','purpose_of_visit','point_of_entry','period_of_stay_days','period_of_stay_text','arrival_date',
        'valid_from','valid_until','flight_carrier','flight_number','flight_details','host_name','host_address',
        'accommodation_type','accommodation_name','booking_reference','booking_confirmation_image_path',
        'host_phone','destination_address','destination_country','is_fee_waived','requires_checker_approval','remarks',
        'applicant_photo_path','employment_status','employer_name','employer_address',
        'emergency_contact_name','emergency_contact_relationship','emergency_contact_phone','emergency_contact_email',
        'guardian_name','guardian_relationship','guardian_address','guardian_phone','guardian_sex',
        'travel_history','immigration_history','security_declarations',
        'submitted_at','applicant_submitted_at','applicant_certified_at','applicant_certification_ip',
        'online_payment_returned_at','reviewed_at','approved_at','last_status_changed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => VisaApplicationStatus::class,
            'arrival_date' => 'date',
            'valid_from' => 'date',
            'valid_until' => 'date',
            'period_of_stay_days' => 'integer',
            'is_fee_waived' => 'boolean',
            'requires_checker_approval' => 'boolean',
            'travel_history' => 'array',
            'immigration_history' => 'array',
            'security_declarations' => 'array',
            'submitted_at' => 'datetime',
            'applicant_submitted_at' => 'datetime',
            'applicant_certified_at' => 'datetime',
            'online_payment_returned_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
            'last_status_changed_at' => 'datetime',
        ];
    }

    public function passenger(): BelongsTo { return $this->belongsTo(Passenger::class); }
    public function airport(): BelongsTo { return $this->belongsTo(Airport::class); }
    public function desk(): BelongsTo { return $this->belongsTo(Desk::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function latestInvoice(): HasOne { return $this->hasOne(Invoice::class)->latestOfMany(); }
    public function payment(): HasOneThrough
    {
        return $this->hasOneThrough(
            Payment::class,
            Invoice::class,
            'visa_application_id',
            'invoice_id',
            'id',
            'id'
        )->latest('payments.created_at');
    }

    public function getReceiptAttribute(): ?Receipt
    {
        return $this->payment?->receipt;
    }

    public function permit(): HasOne { return $this->hasOne(Permit::class); }
    public function waiverApprovals(): HasMany { return $this->hasMany(WaiverApproval::class); }
    public function latestWaiverApproval(): HasOne { return $this->hasOne(WaiverApproval::class)->latestOfMany(); }
    public function fraudFlags(): HasMany { return $this->hasMany(FraudFlag::class); }
    public function admissibilityScreenings(): HasMany { return $this->hasMany(AdmissibilityScreening::class); }
    public function borderMovements(): HasMany { return $this->hasMany(BorderMovement::class); }

    public function pointOfEntry(): BelongsTo { return $this->belongsTo(PointOfEntry::class); }
}
