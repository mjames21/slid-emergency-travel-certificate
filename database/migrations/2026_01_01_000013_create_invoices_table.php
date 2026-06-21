<?php

use App\Enums\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('visa_application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('USD');
            $table->string('payment_reference')->unique();
            $table->string('gateway')->default('wangov');
            $table->string('status')->default(InvoiceStatus::Pending->value);

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['status', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
