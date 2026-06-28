<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'invoice_no' => 'ETC-INV-'.now()->format('Ymd').'-'.$this->faker->unique()->numberBetween(10000, 99999),
            'visa_application_id' => VisaApplication::factory(),
            'created_by' => User::factory(),
            'amount' => 100.00,
            'currency' => 'USD',
            'payment_reference' => 'ETC-PAY-'.now()->format('Ymd').'-'.$this->faker->unique()->numberBetween(1000, 9999),
            'gateway' => 'wangov',
            'status' => $this->faker->randomElement([
                InvoiceStatus::Pending,
                InvoiceStatus::Paid,
            ]),
            'issued_at' => now(),
            'expires_at' => now()->addDay(),
            'paid_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }
}
