<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('visa_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel');
            $table->string('recipient')->nullable();
            $table->string('subject')->nullable();
            $table->string('status')->default('pending');
            $table->string('provider_message_id')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
