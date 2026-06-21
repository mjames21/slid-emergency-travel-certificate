<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffTitle extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'allowed_statuses',
        'can_view_all',
        'can_invite_staff',
        'can_approve_waiver',
        'can_authorize_reprint',
        'can_revoke_permit',
        'can_manage_devices',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'allowed_statuses' => 'array',
            'can_view_all' => 'boolean',
            'can_invite_staff' => 'boolean',
            'can_approve_waiver' => 'boolean',
            'can_authorize_reprint' => 'boolean',
            'can_revoke_permit' => 'boolean',
            'can_manage_devices' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_staff_titles')
            ->withPivot(['assigned_by_user_id', 'assigned_at', 'is_primary'])
            ->withTimestamps();
    }

    public function workflowTransitions(): HasMany
    {
        return $this->hasMany(StaffTitleWorkflowTransition::class);
    }

    public function staffInvitations(): HasMany
    {
        return $this->hasMany(StaffInvitation::class);
    }
}
