<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('device_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('desk_id')->nullable()->constrained('desks')->nullOnDelete();
            $table->string('device_name');
            $table->string('device_identifier')->unique();
            $table->string('hostname')->nullable();
            $table->string('printer_name')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('trusted')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('registered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('device_registrations');
    }
};
