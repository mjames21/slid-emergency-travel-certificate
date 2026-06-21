<?php
// FILE: database/migrations/2026_06_13_000001_create_points_of_entry_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('points_of_entry')) {
            return;
        }

        Schema::create('points_of_entry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('airport_id')
                ->constrained('airports')
                ->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['airport_id', 'is_active']);
            $table->unique(['airport_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_of_entry');
    }
};