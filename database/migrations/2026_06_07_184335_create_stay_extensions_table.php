<?php
// FILE: database/migrations/xxxx_xx_xx_create_stay_extensions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stay_extensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('original_permit_id')->constrained('permits')->cascadeOnDelete();
            $table->foreignId('new_permit_id')->nullable()->constrained('permits')->nullOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('extension_requested');
            $table->unsignedInteger('requested_extra_days');
            $table->date('current_valid_until');
            $table->date('requested_valid_until');
            $table->date('approved_valid_until')->nullable();
            $table->string('reason_category')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('is_fee_waived')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stay_extensions');
    }
};