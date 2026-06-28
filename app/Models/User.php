<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'staff_number',
        'job_title',
        'phone',
        'active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    protected $appends = [
        'profile_photo_url',
    ];

    public function staffTitles(): BelongsToMany
    {
        return $this->belongsToMany(StaffTitle::class, 'user_staff_titles')
            ->withPivot(['assigned_by_user_id', 'assigned_at', 'is_primary'])
            ->withTimestamps();
    }

    public function visaApplicationsCreated(): HasMany
    {
        return $this->hasMany(VisaApplication::class, 'created_by');
    }

    public function visaApplicationsSubmitted(): HasMany
    {
        return $this->hasMany(VisaApplication::class, 'submitted_by');
    }

    public function visaApplicationsApproved(): HasMany
    {
        return $this->hasMany(VisaApplication::class, 'approved_by');
    }

    public function visaApplicationsReviewed(): HasMany
    {
        return $this->hasMany(VisaApplication::class, 'reviewed_by');
    }

    public function issuedPermits(): HasMany
    {
        return $this->hasMany(Permit::class, 'issued_by');
    }

    public function permitPrintLogs(): HasMany
    {
        return $this->hasMany(PermitPrintLog::class, 'printed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function hasStaffTitle(string $code): bool
    {
        return $this->staffTitles->contains(fn (StaffTitle $title) => $title->code === $code);
    }

    public function hasAnyStaffTitle(array $codes): bool
    {
        return $this->staffTitles->contains(fn (StaffTitle $title) => in_array($title->code, $codes, true));
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
