<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_title_id')->constrained()->restrictOnDelete();
            $table->foreignId('airport_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('desk_id')->nullable()->constrained('desks')->nullOnDelete();
            $table->foreignId('invited_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email')->index();
            $table->string('token_hash');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invitations');
    }
};
