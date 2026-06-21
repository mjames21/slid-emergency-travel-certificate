<?php
// FILE: database/migrations/2026_06_13_000002_add_point_of_entry_id_to_visa_applications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('visa_applications')) {
            return;
        }

        Schema::table('visa_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('visa_applications', 'point_of_entry_id')) {
                $table->foreignId('point_of_entry_id')
                    ->nullable()
                    ->after('airport_id')
                    ->constrained('points_of_entry')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('visa_applications')) {
            return;
        }

        Schema::table('visa_applications', function (Blueprint $table) {
            if (Schema::hasColumn('visa_applications', 'point_of_entry_id')) {
                $table->dropConstrainedForeignId('point_of_entry_id');
            }
        });
    }
};
