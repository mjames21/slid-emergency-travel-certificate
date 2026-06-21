<?php

use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('gateway')->default('wangov');
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->string('gateway_reference')->nullable()->index();
            $table->string('payment_channel')->nullable();

            $table->decimal('amount_due', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('currency', 10)->default('USD');

            $table->string('status')->default(PaymentStatus::Pending->value);
            $table->json('raw_payload')->nullable();
            $table->json('verification_payload')->nullable();

            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
