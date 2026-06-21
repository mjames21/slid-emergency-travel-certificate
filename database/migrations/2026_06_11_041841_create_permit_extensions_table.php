<?php
// FILE: database/migrations/xxxx_xx_xx_create_permit_extensions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permit_extensions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('original_permit_id')->constrained('permits')->cascadeOnDelete();
            $table->foreignId('new_permit_id')->nullable()->constrained('permits')->nullOnDelete();

            $table->foreignId('visa_application_id')->constrained('visa_applications')->cascadeOnDelete();
            $table->foreignId('passenger_id')->constrained('passengers')->cascadeOnDelete();

            $table->string('extension_no')->unique();
            $table->unsignedInteger('requested_extra_days');
            $table->date('current_valid_until');
            $table->date('requested_new_valid_until');

            $table->string('reason_code')->nullable();
            $table->text('reason');
            $table->boolean('is_fee_waived')->default(false);
            $table->decimal('fee_amount', 10, 2)->default(0);

            $table->string('status')->default('pending');
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('decision_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_extensions');
    }
};