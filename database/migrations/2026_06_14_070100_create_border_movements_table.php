<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('border_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admissibility_screening_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('visa_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('permit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('passenger_id')->constrained()->restrictOnDelete();
            $table->foreignId('airport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('point_of_entry_id')->nullable()->constrained('points_of_entry')->nullOnDelete();
            $table->foreignId('officer_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('movement_reference')->unique();
            $table->string('movement_type');
            $table->string('decision');
            $table->string('risk_level')->default('low');
            $table->string('screening_status')->default('clear');

            $table->string('passport_number')->index();
            $table->string('nationality_code', 3)->nullable();
            $table->string('carrier')->nullable();
            $table->string('flight_number')->nullable();
            $table->date('permit_valid_until')->nullable();
            $table->integer('overstay_days')->default(0);

            $table->timestamp('occurred_at');
            $table->text('officer_notes')->nullable();
            $table->json('decision_reasons')->nullable();
            $table->timestamps();

            $table->index(['movement_type', 'decision', 'occurred_at']);
            $table->index(['airport_id', 'occurred_at']);
            $table->index(['permit_id', 'movement_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('border_movements');
    }
};
