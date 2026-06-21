<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('visa_applications', 'applicant_category')) {
                $table->string('applicant_category')->nullable()->after('application_channel');
            }

            if (! Schema::hasColumn('visa_applications', 'regional_category')) {
                $table->string('regional_category')->nullable()->after('applicant_category');
            }

            if (! Schema::hasColumn('visa_applications', 'identity_document_type')) {
                $table->string('identity_document_type')->nullable()->after('regional_category');
            }

            if (! Schema::hasColumn('visa_applications', 'identity_document_number')) {
                $table->string('identity_document_number')->nullable()->after('identity_document_type');
            }

            if (! Schema::hasColumn('visa_applications', 'place_of_birth')) {
                $table->string('place_of_birth')->nullable()->after('identity_document_number');
            }

            if (! Schema::hasColumn('visa_applications', 'marital_status')) {
                $table->string('marital_status')->nullable()->after('place_of_birth');
            }

            if (! Schema::hasColumn('visa_applications', 'applicant_address')) {
                $table->text('applicant_address')->nullable()->after('marital_status');
            }

            if (! Schema::hasColumn('visa_applications', 'destination_country')) {
                $table->string('destination_country')->nullable()->after('destination_address');
            }

            if (! Schema::hasColumn('visa_applications', 'guardian_name')) {
                $table->string('guardian_name')->nullable()->after('emergency_contact_email');
            }

            if (! Schema::hasColumn('visa_applications', 'guardian_relationship')) {
                $table->string('guardian_relationship')->nullable()->after('guardian_name');
            }

            if (! Schema::hasColumn('visa_applications', 'guardian_address')) {
                $table->text('guardian_address')->nullable()->after('guardian_relationship');
            }

            if (! Schema::hasColumn('visa_applications', 'guardian_phone')) {
                $table->string('guardian_phone', 50)->nullable()->after('guardian_address');
            }

            if (! Schema::hasColumn('visa_applications', 'guardian_sex')) {
                $table->string('guardian_sex', 20)->nullable()->after('guardian_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            foreach ([
                'guardian_sex',
                'guardian_phone',
                'guardian_address',
                'guardian_relationship',
                'guardian_name',
                'destination_country',
                'applicant_address',
                'marital_status',
                'place_of_birth',
                'identity_document_number',
                'identity_document_type',
                'regional_category',
                'applicant_category',
            ] as $column) {
                if (Schema::hasColumn('visa_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
