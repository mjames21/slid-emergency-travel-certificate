<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('visa_applications', 'accommodation_type')) {
                $table->string('accommodation_type')->nullable()->after('flight_details');
            }

            if (! Schema::hasColumn('visa_applications', 'accommodation_name')) {
                $table->string('accommodation_name')->nullable()->after('accommodation_type');
            }

            if (! Schema::hasColumn('visa_applications', 'booking_reference')) {
                $table->string('booking_reference')->nullable()->after('accommodation_name');
            }

            if (! Schema::hasColumn('visa_applications', 'booking_confirmation_image_path')) {
                $table->string('booking_confirmation_image_path')->nullable()->after('booking_reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visa_applications', function (Blueprint $table) {
            foreach ([
                'booking_confirmation_image_path',
                'booking_reference',
                'accommodation_name',
                'accommodation_type',
            ] as $column) {
                if (Schema::hasColumn('visa_applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
