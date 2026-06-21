<?php

namespace App\Services\Mrz;

class VisaMrzValidator
{
    public function validate(?string $line1, ?string $line2): array
    {
        $line1 = strtoupper(trim((string) $line1));
        $line2 = strtoupper(trim((string) $line2));
        $errors = [];
        $length = strlen($line1);

        if (! in_array($length, [36, 44], true)) {
            $errors[] = 'Visa MRZ line 1 must be 44 characters for MRV-A or 36 characters for MRV-B.';
        }

        if (strlen($line2) !== $length) {
            $errors[] = 'Visa MRZ line 2 must match line 1 length.';
        }

        if ($errors !== []) {
            return $this->result(false, $errors);
        }

        if (! str_starts_with($line1, 'V<')) {
            $errors[] = 'Visa MRZ line 1 must start with V<.';
        }

        $documentType = $length === 44 ? 'MRV-A' : 'MRV-B';
        $issuingState = substr($line1, 2, 3);
        $passportNumber = substr($line2, 0, 9);
        $passportNumberCheck = substr($line2, 9, 1);
        $nationality = substr($line2, 10, 3);
        $birthDate = substr($line2, 13, 6);
        $birthDateCheck = substr($line2, 19, 1);
        $sex = substr($line2, 20, 1);
        $expiry = substr($line2, 21, 6);
        $expiryCheck = substr($line2, 27, 1);
        $optionalData = substr($line2, 28);

        $checks = [
            'passport_number' => $this->matchesCheckDigit($passportNumber, $passportNumberCheck),
            'date_of_birth' => $this->matchesCheckDigit($birthDate, $birthDateCheck),
            'visa_expiry_date' => $this->matchesCheckDigit($expiry, $expiryCheck),
        ];

        foreach ($checks as $name => $passed) {
            if (! $passed) {
                $errors[] = str_replace('_', ' ', ucfirst($name)) . ' check digit failed.';
            }
        }

        if (! preg_match('/^[A-Z<]{3}$/', $issuingState)) {
            $errors[] = 'Issuing state must be a three-letter ICAO code.';
        }

        if (! preg_match('/^[A-Z<]{3}$/', $nationality)) {
            $errors[] = 'Nationality must be a three-letter ICAO code.';
        }

        if (! in_array($sex, ['M', 'F', '<'], true)) {
            $errors[] = 'Sex marker must be M, F, or <.';
        }

        return [
            'ok' => $errors === [],
            'errors' => $errors,
            'document_type' => $documentType,
            'issuing_state' => str_replace('<', '', $issuingState),
            'passport_number' => rtrim(str_replace('<', '', $passportNumber)),
            'nationality_code' => str_replace('<', '', $nationality),
            'date_of_birth' => $this->convertDate($birthDate, false),
            'sex' => $sex === '<' ? null : $sex,
            'visa_expiry_date' => $this->convertDate($expiry, true),
            'optional_data' => rtrim($optionalData, '<'),
            'checks' => $checks,
            'confidence' => (int) round((collect($checks)->filter(fn ($passed) => $passed === true)->count() / count($checks)) * 100),
        ];
    }

    protected function result(bool $ok, array $errors): array
    {
        return [
            'ok' => $ok,
            'errors' => $errors,
            'checks' => [],
            'confidence' => 0,
        ];
    }

    protected function matchesCheckDigit(string $data, string $checkDigit): bool
    {
        if (! preg_match('/^[0-9<]$/', $checkDigit)) {
            return false;
        }

        return (string) $this->computeCheckDigit($data) === $checkDigit;
    }

    protected function computeCheckDigit(string $data): int
    {
        $weights = [7, 3, 1];
        $sum = 0;

        foreach (str_split($data) as $index => $char) {
            $sum += $this->charValue($char) * $weights[$index % 3];
        }

        return $sum % 10;
    }

    protected function charValue(string $char): int
    {
        if ($char === '<') {
            return 0;
        }

        if (ctype_digit($char)) {
            return (int) $char;
        }

        return ord($char) - 55;
    }

    protected function convertDate(string $value, bool $futureCentury): ?string
    {
        if (! preg_match('/^\d{6}$/', $value)) {
            return null;
        }

        $yy = (int) substr($value, 0, 2);
        $mm = (int) substr($value, 2, 2);
        $dd = (int) substr($value, 4, 2);
        $currentYear = (int) date('Y');
        $century = (int) floor($currentYear / 100) * 100;
        $year = $yy + $century;

        if ($futureCentury && $year < $currentYear - 5) {
            $year += 100;
        }

        if ($futureCentury && $year > $currentYear + 20) {
            $year -= 100;
        }

        if (! $futureCentury && $year > $currentYear) {
            $year -= 100;
        }

        if (! checkdate($mm, $dd, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $mm, $dd);
    }
}
