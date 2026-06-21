<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permit_print_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('printed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('airport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('desk_id')->nullable()->constrained('desks')->nullOnDelete();
            $table->unsignedBigInteger('device_registration_id')->nullable();
            $table->string('terminal_name')->nullable();
            $table->string('printer_name')->nullable();
            $table->boolean('is_reprint')->default(false);
            $table->string('reason_code')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_print_logs');
    }
};
