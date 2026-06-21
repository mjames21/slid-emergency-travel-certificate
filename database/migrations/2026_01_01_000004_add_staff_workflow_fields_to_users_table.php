<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('primary_airport_id')->nullable()->after('id')->constrained('airports')->nullOnDelete();
            $table->foreignId('primary_desk_id')->nullable()->after('primary_airport_id')->constrained('desks')->nullOnDelete();
            $table->string('staff_number')->nullable()->unique()->after('email');
            $table->string('job_title')->nullable()->after('staff_number');
            $table->string('phone', 50)->nullable()->after('job_title');
            $table->boolean('active')->default(true)->after('phone');
            $table->timestamp('last_login_at')->nullable()->after('active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_airport_id');
            $table->dropConstrainedForeignId('primary_desk_id');
            $table->dropColumn(['staff_number', 'job_title', 'phone', 'active', 'last_login_at']);
        });
    }
};
