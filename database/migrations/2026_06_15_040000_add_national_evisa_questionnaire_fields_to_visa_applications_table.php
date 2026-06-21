<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('visa_applications', 'applicant_photo_path')) {
                $table->string('applicant_photo_path')->nullable()->after('booking_confirmation_image_path');
            }

            if (! Schema::hasColumn('visa_applications', 'employment_status')) {
                $table->string('employment_status')->nullable()->after('destination_address');
            }

            if (! Schema::hasColumn('visa_applications', 'employer_name')) {
                $table->string('employer_name')->nullable()->after('employment_status');
            }

            if (! Schema::hasColumn('visa_applications', 'employer_address')) {
                $table->text('employer_address')->nullable()->after('employer_name');
            }

            if (! Schema::hasColumn('visa_applications', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('employer_address');
            }

            if (! Schema::hasColumn('visa_applications', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->after('emergency_contact_name');
            }

            if (! Schema::hasColumn('visa_applications', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone', 50)->nullable()->after('emergency_contact_relationship');
            }

            if (! Schema::hasColumn('visa_applications', 'emergency_contact_email')) {
                $table->string('emergency_contact_email')->nullable()->after('emergency_contact_phone');
            }

            if (! Schema::hasColumn('visa_applications', 'travel_history')) {
                $table->json('travel_history')->nullable()->after('emergency_contact_email');
            }

            if (! Schema::hasColumn('visa_applications', 'immigration_history')) {
                $table->json('immigration_history')->nullable()->after('travel_history');
            }

            if (! Schema::hasColumn('visa_applications', 'security_declarations')) {
                $table->json('security_declarations')->nullable()->after('immigration_history');
            }

            if (! Schema::hasColumn('visa_applications', 'applicant_certified_at')) {
                $table->timestamp('applicant_certified_at')->nullable()->after('applicant_submitted_at');
            }

            if (! Schema::hasColumn('visa_applications', 'applicant_certification_ip')) {
                $table->string('applicant_certification_ip', 64)->nullable()->after('applicant_certified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            foreach ([
                'applicant_certification_ip',
                'applicant_certified_at',
                'security_declarations',
                'immigration_history',
                'travel_history',
                'emergency_contact_email',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'emergency_contact_name',
                'employer_address',
                'employer_name',
                'employment_status',
                'applicant_photo_path',
            ] as $column) {
                if (Schema::hasColumn('visa_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
