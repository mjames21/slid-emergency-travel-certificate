<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRoleChange extends Model
{
    protected $fillable = ['user_id','actor_user_id','reason_category','reason','before_roles','after_roles','changed_at'];

    protected function casts(): array
    {
        return [
            'before_roles' => 'array',
            'after_roles' => 'array',
            'changed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
