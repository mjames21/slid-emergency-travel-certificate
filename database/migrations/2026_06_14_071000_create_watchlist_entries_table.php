<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('watchlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('internal');
            $table->string('category')->default('immigration_alert');
            $table->string('severity')->default('medium');
            $table->string('status')->default('active');
            $table->string('passport_number')->nullable()->index();
            $table->string('nationality_code', 3)->nullable()->index();
            $table->string('surname')->nullable()->index();
            $table->string('given_names')->nullable();
            $table->date('date_of_birth')->nullable()->index();
            $table->text('reason');
            $table->text('instructions')->nullable();
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity']);
            $table->index(['source', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_entries');
    }
};
