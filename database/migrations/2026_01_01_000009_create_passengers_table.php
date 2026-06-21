<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->string('surname');
            $table->string('given_names');
            $table->string('full_name');
            $table->string('nationality');
            $table->string('nationality_code', 3)->nullable();
            $table->string('passport_number')->index();
            $table->date('passport_expiry_date');
            $table->string('sex', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country_of_birth')->nullable();
            $table->string('country_of_residence')->nullable();
            $table->string('occupation')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();

            $table->string('passport_biodata_image_path')->nullable();
            $table->timestamp('passport_biodata_captured_at')->nullable();
            $table->foreignId('passport_biodata_captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('passport_biodata_capture_device')->nullable();

            $table->timestamps();

            $table->unique(['passport_number', 'nationality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passengers');
    }
};