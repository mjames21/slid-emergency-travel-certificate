<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'confirmed_by' => User::factory(),
            'gateway' => 'wangov',
            'gateway_transaction_id' => 'WANGOV-'.strtoupper($this->faker->unique()->bothify('****************')),
            'gateway_reference' => 'ETC-PAY-'.now()->format('Ymd').'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'payment_channel' => 'wangov_checkout',
            'amount_due' => 100.00,
            'amount_paid' => 100.00,
            'currency' => 'USD',
            'status' => PaymentStatus::Successful,
            'raw_payload' => ['status' => 'success'],
            'verification_payload' => ['verified' => true],
            'initiated_at' => now()->subMinutes(5),
            'paid_at' => now()->subMinutes(3),
            'verified_at' => now()->subMinutes(2),
            'failed_at' => null,
            'failure_reason' => null,
        ];
    }
}
