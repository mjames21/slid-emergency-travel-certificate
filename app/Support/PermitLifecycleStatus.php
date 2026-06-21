<?php

namespace App\Support;

use App\Models\Permit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class PermitLifecycleStatus
{
    public static function column(): ?string
    {
        if (! Schema::hasTable('permits')) {
            return null;
        }

        if (Schema::hasColumn('permits', 'lifecycle_status')) {
            return 'lifecycle_status';
        }

        if (Schema::hasColumn('permits', 'permit_status')) {
            return 'permit_status';
        }

        return null;
    }

    public static function constrainActive(Builder $query): Builder
    {
        $column = self::column();

        if (! $column) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($column) {
            $query->whereNull($column)
                ->orWhere($column, 'active');
        });
    }

    public static function value(?Permit $permit, string $default = 'active'): string
    {
        if (! $permit) {
            return $default;
        }

        return (string) ($permit->lifecycle_status ?? $permit->permit_status ?? $default);
    }

    public static function set(Permit $permit, string $status): void
    {
        $column = self::column();

        if (! $column) {
            return;
        }

        $permit->{$column} = $status;
    }
}
