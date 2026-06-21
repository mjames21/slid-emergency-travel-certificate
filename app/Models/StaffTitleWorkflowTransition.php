<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffTitleWorkflowTransition extends Model
{
    protected $fillable = ['staff_title_id','from_status_key','action','to_status_key','sort_order','requires_reason','requires_checker','active'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'requires_reason' => 'boolean',
            'requires_checker' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function staffTitle(): BelongsTo
    {
        return $this->belongsTo(StaffTitle::class);
    }
}
