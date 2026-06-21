<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('travel_requirement_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('sl_immigration');
            $table->string('nationality_code', 3)->nullable()->index();
            $table->string('document_type')->default('passport');
            $table->string('visa_type')->default('visa_on_arrival');
            $table->string('purpose_of_visit')->nullable()->index();
            $table->string('carrier_code')->nullable()->index();
            $table->integer('max_stay_days')->nullable();
            $table->integer('min_passport_validity_days')->default(0);
            $table->boolean('visa_required')->default(true);
            $table->boolean('return_ticket_required')->default(false);
            $table->boolean('host_address_required')->default(true);
            $table->boolean('active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['active', 'visa_type']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_requirement_rules');
    }
};
