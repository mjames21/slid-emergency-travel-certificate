<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_document_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('internal');
            $table->string('document_type')->default('passport');
            $table->string('document_status')->default('lost');
            $table->string('document_number')->index();
            $table->string('issuing_state', 3)->nullable()->index();
            $table->date('date_of_birth')->nullable();
            $table->string('holder_name')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('reported_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['document_status', 'source']);
            $table->unique(['document_number', 'issuing_state', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_document_alerts');
    }
};
