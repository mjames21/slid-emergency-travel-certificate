<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('border_movements', function (Blueprint $table) {
            if (! Schema::hasColumn('border_movements', 'is_supervisor_override')) {
                $table->boolean('is_supervisor_override')->default(false)->after('overstay_days');
            }

            if (! Schema::hasColumn('border_movements', 'supervisor_override_reason')) {
                $table->text('supervisor_override_reason')->nullable()->after('is_supervisor_override');
            }
        });
    }

    public function down(): void
    {
        Schema::table('border_movements', function (Blueprint $table) {
            if (Schema::hasColumn('border_movements', 'supervisor_override_reason')) {
                $table->dropColumn('supervisor_override_reason');
            }

            if (Schema::hasColumn('border_movements', 'is_supervisor_override')) {
                $table->dropColumn('is_supervisor_override');
            }
        });
    }
};
