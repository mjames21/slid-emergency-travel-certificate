<?php
// FILE: database/migrations/2026_01_01_000025_add_visa_id_to_permits_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->string('visa_id')->nullable()->unique()->after('permit_no');
        });
    }

    public function down(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->dropColumn('visa_id');
        });
    }
};