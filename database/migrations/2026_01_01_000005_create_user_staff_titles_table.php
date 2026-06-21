<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_staff_titles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_title_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'staff_title_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_staff_titles');
    }
};
