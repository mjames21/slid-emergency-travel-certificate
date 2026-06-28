<?php

namespace Database\Factories;

use App\Enums\PermitStatus;
use App\Models\Payment;
use App\Models\Permit;
use App\Models\User;
use App\Models\VisaApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

class PermitFactory extends Factory
{
    protected $model = Permit::class;

    public function definition(): array
    {
        return [
            'permit_no' => 'ETC-CERT-'.now()->format('Y').'-'.$this->faker->unique()->numberBetween(100000, 999999),
            'visa_application_id' => VisaApplication::factory(),
            'payment_id' => Payment::factory(),
            'issued_by' => User::factory(),
            'permit_type' => VisaApplication::TYPE_EMERGENCY_TRAVEL_CERTIFICATE,
            'status' => PermitStatus::Issued,
            'issued_at' => now(),
            'valid_from' => now()->toDateString(),
            'valid_until' => now()->addMonth()->toDateString(),
            'verification_code' => 'VRY-'.strtoupper(bin2hex(random_bytes(10))),
            'security_seal' => hash('sha256', (string) fake()->uuid()),
            'seal_algorithm' => 'hmac-sha256',
            'seal_version' => 'v1',
            'qr_code_path' => null,
            'document_path' => null,
            'document_hash' => hash('sha256', (string) fake()->uuid()),
            'virtual_payload_hash' => hash('sha256', (string) fake()->uuid()),
            'mrz_type' => 'MRV-A',
            'mrz_line_1' => str_pad('V<SLEDOE<<JOHN<<<<<<<<<<<<<<<<<<<<<<<<<<<<', 44, '<'),
            'mrz_line_2' => str_pad('U12345678<1SLE9001011M2601012ABC1234567890<1', 44, '<'),
            'print_count' => 1,
            'last_printed_at' => now(),
            'is_virtual_available' => true,
            'is_duplicate_print' => false,
            'cancelled_at' => null,
            'revoked_at' => null,
            'revocation_reason' => null,
        ];
    }
}
