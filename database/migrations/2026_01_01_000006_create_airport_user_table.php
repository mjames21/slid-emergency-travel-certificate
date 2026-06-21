<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('airport_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airport_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('desk_id')->nullable()->constrained('desks')->nullOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['airport_id', 'user_id', 'desk_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('airport_user');
    }
};
