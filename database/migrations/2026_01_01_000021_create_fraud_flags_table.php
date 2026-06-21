<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fraud_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('permit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('flagged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('flag_type');
            $table->string('severity')->default('medium');
            $table->text('description');
            $table->boolean('resolved')->default(false);
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('flagged_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_flags');
    }
};
