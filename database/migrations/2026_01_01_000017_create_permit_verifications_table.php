<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permit_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('verification_code')->index();
            $table->string('result');
            $table->string('channel')->default('public_portal');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permit_verifications');
    }
};
