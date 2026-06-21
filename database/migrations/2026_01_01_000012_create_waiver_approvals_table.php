<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waiver_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason_category');
            $table->text('reason');
            $table->string('authority_reference')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiver_approvals');
    }
};
