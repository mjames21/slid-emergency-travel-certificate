<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('permit_print_logs', function (Blueprint $table) {
            $table->foreign('device_registration_id')
                ->references('id')
                ->on('device_registrations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('permit_print_logs', function (Blueprint $table) {
            $table->dropForeign(['device_registration_id']);
        });
    }
};
