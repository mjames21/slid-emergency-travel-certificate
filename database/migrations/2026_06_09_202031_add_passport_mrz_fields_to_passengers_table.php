<?php

// FILE: database/migrations/2026_06_09_000001_add_passport_mrz_fields_to_passengers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            if (! Schema::hasColumn('passengers', 'passport_mrz_image_path')) {
                $table->string('passport_mrz_image_path')->nullable()->after('passport_biodata_image_path');
            }

            if (! Schema::hasColumn('passengers', 'passport_mrz_raw')) {
                $table->text('passport_mrz_raw')->nullable()->after('passport_mrz_image_path');
            }

            if (! Schema::hasColumn('passengers', 'passport_mrz_data')) {
                $table->json('passport_mrz_data')->nullable()->after('passport_mrz_raw');
            }

            if (! Schema::hasColumn('passengers', 'passport_mrz_confidence')) {
                $table->decimal('passport_mrz_confidence', 5, 2)->nullable()->after('passport_mrz_data');
            }

            if (! Schema::hasColumn('passengers', 'passport_mrz_extracted_at')) {
                $table->timestamp('passport_mrz_extracted_at')->nullable()->after('passport_mrz_confidence');
            }

            if (! Schema::hasColumn('passengers', 'passport_mrz_extracted_by')) {
                $table->foreignId('passport_mrz_extracted_by')
                    ->nullable()
                    ->after('passport_mrz_extracted_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('passengers', function (Blueprint $table) {
            $dropColumns = [];

            if (Schema::hasColumn('passengers', 'passport_mrz_extracted_by')) {
                $table->dropConstrainedForeignId('passport_mrz_extracted_by');
            }

            foreach ([
                'passport_mrz_image_path',
                'passport_mrz_raw',
                'passport_mrz_data',
                'passport_mrz_confidence',
                'passport_mrz_extracted_at',
            ] as $column) {
                if (Schema::hasColumn('passengers', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};