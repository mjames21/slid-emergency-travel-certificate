<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_title_workflow_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_title_id')->constrained()->cascadeOnDelete();
            $table->string('from_status_key');
            $table->string('action');
            $table->string('to_status_key');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('requires_reason')->default(false);
            $table->boolean('requires_checker')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['staff_title_id', 'from_status_key', 'action', 'to_status_key'], 'workflow_transition_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_title_workflow_transitions');
    }
};
