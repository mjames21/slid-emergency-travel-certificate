<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('visa_applications', 'application_channel')) {
                $table->string('application_channel')->default('staff')->after('visa_type');
            }

            if (! Schema::hasColumn('visa_applications', 'public_tracking_code')) {
                $table->string('public_tracking_code')->nullable()->unique()->after('application_no');
            }

            if (! Schema::hasColumn('visa_applications', 'public_access_token')) {
                $table->string('public_access_token')->nullable()->unique()->after('public_tracking_code');
            }

            if (! Schema::hasColumn('visa_applications', 'applicant_submitted_at')) {
                $table->timestamp('applicant_submitted_at')->nullable()->after('submitted_at');
            }

            if (! Schema::hasColumn('visa_applications', 'online_payment_returned_at')) {
                $table->timestamp('online_payment_returned_at')->nullable()->after('applicant_submitted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            foreach ([
                'online_payment_returned_at',
                'applicant_submitted_at',
                'public_access_token',
                'public_tracking_code',
                'application_channel',
            ] as $column) {
                if (Schema::hasColumn('visa_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
