<?php

use App\Enums\VisaApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no')->unique();
            $table->foreignId('passenger_id')->constrained()->restrictOnDelete();
            $table->foreignId('airport_id')->constrained()->restrictOnDelete();
            $table->foreignId('desk_id')->nullable()->constrained('desks')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('visa_type')->default('visa_on_arrival');
            $table->string('status')->default(VisaApplicationStatus::Draft->value);

            $table->string('purpose_of_visit');
            $table->string('point_of_entry');
            $table->unsignedInteger('period_of_stay_days');
            $table->string('period_of_stay_text')->nullable();
            $table->date('arrival_date');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->string('flight_carrier')->nullable();
            $table->string('flight_number')->nullable();
            $table->text('flight_details')->nullable();

            $table->string('host_name')->nullable();
            $table->text('host_address')->nullable();
            $table->string('host_phone', 50)->nullable();
            $table->text('destination_address')->nullable();

            $table->boolean('is_fee_waived')->default(false);
            $table->boolean('requires_checker_approval')->default(false);
            $table->text('remarks')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_status_changed_at')->nullable();

            $table->timestamps();

            $table->index(['status', 'airport_id']);
            $table->index(['created_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
