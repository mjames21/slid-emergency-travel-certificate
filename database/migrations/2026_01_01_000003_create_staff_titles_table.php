<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff_titles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->json('allowed_statuses')->nullable();
            $table->boolean('can_view_all')->default(false);
            $table->boolean('can_invite_staff')->default(false);
            $table->boolean('can_approve_waiver')->default(false);
            $table->boolean('can_authorize_reprint')->default(false);
            $table->boolean('can_revoke_permit')->default(false);
            $table->boolean('can_manage_devices')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_titles');
    }
};
