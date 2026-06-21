<?php

namespace Tests\Unit;

use App\Models\Permit;
use App\Services\Mrz\VisaMrzValidator;
use App\Support\MrzGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VisaMrzValidatorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_validates_generated_mrv_a_lines(): void
    {
        $permit = Permit::factory()->create([
            'payment_id' => null,
            'valid_until' => now()->addMonth()->toDateString(),
        ]);

        $mrz = app(MrzGenerator::class)->generate($permit->fresh(['visaApplication.passenger']));
        $result = app(VisaMrzValidator::class)->validate($mrz['line_1'], $mrz['line_2']);

        $this->assertTrue($result['ok']);
        $this->assertSame(100, $result['confidence']);
        $this->assertSame('MRV-A', $result['document_type']);
        $this->assertSame('MRV-A', $mrz['type']);
        $this->assertSame(44, strlen($mrz['line_1']));
        $this->assertSame(44, strlen($mrz['line_2']));
    }

    #[Test]
    public function it_validates_mrv_b_lines_for_smaller_visa_formats(): void
    {
        $line1 = str_pad('V<SLEJAMES<<MOHAMED', 36, '<');
        $passportNumber = str_pad('SLR092377', 9, '<');
        $birthDate = '860421';
        $expiryDate = '290312';
        $line2 = $passportNumber
            . $this->checkDigit($passportNumber)
            . 'SLE'
            . $birthDate
            . $this->checkDigit($birthDate)
            . 'M'
            . $expiryDate
            . $this->checkDigit($expiryDate)
            . str_pad('SLID', 8, '<');

        $result = app(VisaMrzValidator::class)->validate($line1, $line2);

        $this->assertTrue($result['ok']);
        $this->assertSame('MRV-B', $result['document_type']);
        $this->assertSame(36, strlen($line1));
        $this->assertSame(36, strlen($line2));
    }

    #[Test]
    public function it_rejects_visa_mrz_check_digit_failures(): void
    {
        $permit = Permit::factory()->create([
            'payment_id' => null,
            'valid_until' => now()->addMonth()->toDateString(),
        ]);

        $mrz = app(MrzGenerator::class)->generate($permit->fresh(['visaApplication.passenger']));
        $badCheckDigit = substr($mrz['line_2'], 9, 1) === '0' ? '1' : '0';
        $line2 = substr_replace($mrz['line_2'], $badCheckDigit, 9, 1);

        $result = app(VisaMrzValidator::class)->validate($mrz['line_1'], $line2);

        $this->assertFalse($result['ok']);
        $this->assertContains('Passport number check digit failed.', $result['errors']);
    }

    private function checkDigit(string $value): string
    {
        $weights = [7, 3, 1];
        $sum = 0;

        foreach (str_split($value) as $index => $char) {
            $sum += $this->charValue($char) * $weights[$index % 3];
        }

        return (string) ($sum % 10);
    }

    private function charValue(string $char): int
    {
        if ($char === '<') {
            return 0;
        }

        if (ctype_digit($char)) {
            return (int) $char;
        }

        return ord($char) - 55;
    }
}
