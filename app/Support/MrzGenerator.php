<?php

namespace App\Support;

use App\Models\Permit;

class MrzGenerator
{
    public function generate(Permit $permit): array
    {
        $passenger = $permit->visaApplication->passenger;

        $surname = $this->sanitize($passenger->surname);
        $givenNames = $this->sanitize(str_replace(' ', '<', $passenger->given_names));
        $nameField = substr($surname . '<<' . $givenNames, 0, 39);
        $nameField = str_pad($nameField, 39, '<');

        $documentCode = 'V<';
        $issuingState = 'SLE';
        $line1 = substr(str_pad($documentCode . $issuingState . $nameField, 44, '<'), 0, 44);

        $passportNumber = str_pad($this->sanitize($passenger->passport_number), 9, '<');
        $nationality = str_pad($passenger->nationality_code ?: 'XXX', 3, '<');
        $birthDate = $passenger->date_of_birth?->format('ymd') ?? '000000';
        $sex = strtoupper(substr((string) $passenger->sex, 0, 1) ?: '<');
        $expiry = $permit->valid_until?->format('ymd') ?? '000000';
        $optionalData = str_pad(substr($this->sanitize($permit->verification_code), 0, 16), 16, '<');

        $line2 = $passportNumber
            . $this->checkDigit($passportNumber)
            . $nationality
            . $birthDate
            . $this->checkDigit($birthDate)
            . $sex
            . $expiry
            . $this->checkDigit($expiry)
            . $optionalData;

        return [
            'type' => 'MRV-A',
            'line_1' => substr($line1, 0, 44),
            'line_2' => substr($line2, 0, 44),
        ];
    }

    protected function sanitize(string $value): string
    {
        $value = strtoupper($value);
        $value = preg_replace('/[^A-Z0-9]/', '<', $value);

        return preg_replace('/<+/', '<', $value) ?: '';
    }

    protected function checkDigit(string $value): string
    {
        $weights = [7, 3, 1];
        $sum = 0;
        $chars = str_split($value);

        foreach ($chars as $index => $char) {
            $sum += $this->charValue($char) * $weights[$index % 3];
        }

        return (string) ($sum % 10);
    }

    protected function charValue(string $char): int
    {
        if ($char === '<') {
            return 0;
        }

        if (is_numeric($char)) {
            return (int) $char;
        }

        return ord($char) - 55;
    }
}
