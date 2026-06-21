<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            if (! Schema::hasColumn('receipts', 'receipt_source')) {
                $table->string('receipt_source')->default('internal')->after('issued_by');
            }

            if (! Schema::hasColumn('receipts', 'evidence_path')) {
                $table->string('evidence_path')->nullable()->after('receipt_source');
            }

            if (! Schema::hasColumn('receipts', 'evidence_original_name')) {
                $table->string('evidence_original_name')->nullable()->after('evidence_path');
            }

            if (! Schema::hasColumn('receipts', 'evidence_mime_type')) {
                $table->string('evidence_mime_type')->nullable()->after('evidence_original_name');
            }

            if (! Schema::hasColumn('receipts', 'evidence_size')) {
                $table->unsignedBigInteger('evidence_size')->nullable()->after('evidence_mime_type');
            }

            if (! Schema::hasColumn('receipts', 'evidence_hash')) {
                $table->string('evidence_hash', 128)->nullable()->after('evidence_size');
            }

            if (! Schema::hasColumn('receipts', 'notes')) {
                $table->text('notes')->nullable()->after('evidence_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            foreach ([
                'receipt_source',
                'evidence_path',
                'evidence_original_name',
                'evidence_mime_type',
                'evidence_size',
                'evidence_hash',
                'notes',
            ] as $column) {
                if (Schema::hasColumn('receipts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
