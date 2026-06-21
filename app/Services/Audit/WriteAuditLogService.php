<?php
// FILE: app/Services/Audit/WriteAuditLogService.php

namespace App\Services\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class WriteAuditLogService
{
    public function handle(
        ?Model $actor,
        string $action,
        Model $subject,
        array $metadata = [],
        ?string $description = null
    ): void {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        try {
            $columns = Schema::getColumnListing('audit_logs');
            $payload = [];

            $actorId = $actor?->getKey();
            $subjectType = $subject->getMorphClass();
            $subjectId = $subject->getKey();
            $now = now();

            $this->setFirstExisting($payload, $columns, ['user_id', 'actor_id', 'staff_user_id'], $actorId);
            $this->setFirstExisting($payload, $columns, ['action', 'event', 'event_type', 'activity'], $action);
            $this->setFirstExisting($payload, $columns, ['auditable_type', 'subject_type', 'model_type'], $subjectType);
            $this->setFirstExisting($payload, $columns, ['auditable_id', 'subject_id', 'model_id'], $subjectId);

            $this->setFirstExisting(
                $payload,
                $columns,
                ['description', 'message', 'summary'],
                $description ?: Str::headline(str_replace(['.', '_'], ' ', $action))
            );

            $jsonValue = json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $this->setFirstExisting($payload, $columns, ['metadata', 'meta', 'properties', 'context', 'payload'], $jsonValue);

            $this->setFirstExisting($payload, $columns, ['ip_address'], request()?->ip());
            $this->setFirstExisting($payload, $columns, ['user_agent'], request()?->userAgent());
            $this->setFirstExisting($payload, $columns, ['logged_at', 'occurred_at'], $now);

            if (in_array('created_at', $columns, true) && ! array_key_exists('created_at', $payload)) {
                $payload['created_at'] = $now;
            }

            if (in_array('updated_at', $columns, true) && ! array_key_exists('updated_at', $payload)) {
                $payload['updated_at'] = $now;
            }

            DB::table('audit_logs')->insert($payload);
        } catch (Throwable $e) {
            report($e);
        }
    }

    protected function setFirstExisting(array &$payload, array $columns, array $candidates, mixed $value): void
    {
        if ($value === null) {
            return;
        }

        foreach ($candidates as $column) {
            if (in_array($column, $columns, true)) {
                $payload[$column] = $value;
                return;
            }
        }
    }
}