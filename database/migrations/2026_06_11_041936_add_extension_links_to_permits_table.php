<?php
// FILE: database/migrations/xxxx_xx_xx_add_extension_links_to_permits_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->foreignId('parent_permit_id')->nullable()->after('id')->constrained('permits')->nullOnDelete();
            $table->boolean('is_extension')->default(false)->after('parent_permit_id');
            $table->string('permit_status')->default('active')->after('is_extension');
        });
    }

    public function down(): void
    {
        Schema::table('permits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_permit_id');
            $table->dropColumn(['is_extension', 'permit_status']);
        });
    }
};