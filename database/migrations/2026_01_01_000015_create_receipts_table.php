<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->unique();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('document_path')->nullable();
            $table->string('document_hash')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->unique(['payment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
