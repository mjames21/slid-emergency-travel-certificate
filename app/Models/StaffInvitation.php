<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffInvitation extends Model
{
    protected $fillable = ['staff_title_id','airport_id','desk_id','invited_by_user_id','email','token_hash','expires_at','accepted_at','active'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function staffTitle(): BelongsTo
    {
        return $this->belongsTo(StaffTitle::class);
    }

    public function airport(): BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

    public function desk(): BelongsTo
    {
        return $this->belongsTo(Desk::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }
}
