<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('policy_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('policy_area');
            $table->string('standard_reference')->nullable();
            $table->string('status')->default('pending');
            $table->string('version')->nullable();
            $table->text('summary');
            $table->json('evidence')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('decision_note')->nullable();
            $table->timestamps();

            $table->index(['policy_area', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_approvals');
    }
};
