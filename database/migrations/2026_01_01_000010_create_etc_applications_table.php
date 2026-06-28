<?php

use App\Enums\VisaApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visa_applications', function (Blueprint $table) {
            $table->id();
            $table->string('application_no')->unique();
            $table->string('public_tracking_code')->nullable()->unique();
            $table->string('public_access_token')->nullable()->unique();
            $table->foreignId('passenger_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('visa_type')->default('emergency_travel_certificate');
            $table->string('application_channel')->default('online_emergency_travel_certificate');
            $table->string('status')->default(VisaApplicationStatus::AwaitingPayment->value);

            $table->string('applicant_category')->nullable();
            $table->string('regional_category')->nullable();
            $table->string('identity_document_type')->nullable();
            $table->string('identity_document_number')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('applicant_address')->nullable();

            $table->string('purpose_of_visit');
            $table->string('point_of_entry')->default('Emergency Travel Certificate Desk');
            $table->unsignedInteger('period_of_stay_days')->default(30);
            $table->string('period_of_stay_text')->nullable();
            $table->date('arrival_date');
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->string('destination_country')->nullable();
            $table->text('destination_address')->nullable();
            $table->string('flight_carrier')->nullable();
            $table->string('flight_number')->nullable();
            $table->text('flight_details')->nullable();
            $table->string('accommodation_type')->nullable();
            $table->string('accommodation_name')->nullable();
            $table->string('booking_reference')->nullable();
            $table->string('booking_confirmation_image_path')->nullable();
            $table->string('applicant_photo_path')->nullable();

            $table->string('host_name')->nullable();
            $table->text('host_address')->nullable();
            $table->string('host_phone', 50)->nullable();
            $table->string('employment_status')->nullable();
            $table->string('employer_name')->nullable();
            $table->text('employer_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone', 50)->nullable();
            $table->string('emergency_contact_email')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('guardian_phone', 50)->nullable();
            $table->string('guardian_sex', 20)->nullable();

            $table->json('travel_history')->nullable();
            $table->json('immigration_history')->nullable();
            $table->json('security_declarations')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('applicant_submitted_at')->nullable();
            $table->timestamp('applicant_certified_at')->nullable();
            $table->string('applicant_certification_ip', 64)->nullable();
            $table->timestamp('online_payment_returned_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('last_status_changed_at')->nullable();

            $table->timestamps();

            $table->index(['application_channel', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visa_applications');
    }
};
