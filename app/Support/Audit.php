<?php

namespace App\Support;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class Audit
{
    public static function log(
        string $action,
        string $description,
        ?Model $auditable = null,
        array $metadata = []
    ): void {
        $user = auth()->user();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'action' => $action,
            'description' => $description,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);
    }
}
