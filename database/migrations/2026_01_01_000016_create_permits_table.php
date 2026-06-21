<?php

use App\Enums\PermitStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('permits', function (Blueprint $table) {
            $table->id();
            $table->string('permit_no')->unique();
            $table->foreignId('visa_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('waiver_approval_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('checker_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('superseded_by_permit_id')->nullable()->constrained('permits')->nullOnDelete();

            $table->string('permit_type')->default('visa_on_arrival');
            $table->string('status')->default(PermitStatus::Generated->value);

            $table->timestamp('issued_at')->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();

            $table->string('verification_code')->unique();
            $table->string('security_seal')->nullable();
            $table->string('seal_algorithm')->nullable();
            $table->string('seal_version')->nullable();

            $table->string('qr_code_path')->nullable();
            $table->string('document_path')->nullable();
            $table->string('document_hash')->nullable();
            $table->string('virtual_payload_hash')->nullable();

            $table->string('mrz_type')->nullable();
            $table->string('mrz_line_1')->nullable();
            $table->string('mrz_line_2')->nullable();

            $table->unsignedInteger('print_count')->default(0);
            $table->timestamp('last_printed_at')->nullable();

            $table->boolean('is_virtual_available')->default(true);
            $table->boolean('is_duplicate_print')->default(false);

            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revocation_reason')->nullable();

            $table->timestamps();

            $table->unique(['visa_application_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permits');
    }
};
