<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('admissibility_screenings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visa_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('permit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('passenger_id')->constrained()->restrictOnDelete();
            $table->foreignId('airport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('screened_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('screening_reference')->unique();
            $table->string('movement_type')->default('entry');
            $table->string('status')->default('clear');
            $table->string('risk_level')->default('low');

            $table->boolean('passport_valid')->default(false);
            $table->boolean('permit_valid')->default(false);
            $table->boolean('mrz_verified')->default(false);
            $table->boolean('traveler_history_reviewed')->default(false);
            $table->boolean('watchlist_checked')->default(false);
            $table->boolean('carrier_document_check')->default(false);
            $table->boolean('protection_referral_required')->default(false);

            $table->json('reasons')->nullable();
            $table->json('recommendations')->nullable();
            $table->text('officer_notes')->nullable();
            $table->timestamp('screened_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'risk_level']);
            $table->index(['passenger_id', 'screened_at']);
            $table->index(['permit_id', 'screened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admissibility_screenings');
    }
};
