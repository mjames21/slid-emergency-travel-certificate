<?php
// FILE: app/Services/Mrz/MrzParser.php

namespace App\Services\Mrz;

class MrzParser
{
    public function parsePassport(string $rawText): array
    {
        $lines = $this->normalizeLines($rawText);

        if (count($lines) < 2) {
            return [
                'ok' => false,
                'message' => 'Could not detect 2 MRZ lines.',
                'raw_text' => $rawText,
                'lines' => $lines,
            ];
        }

        $line1 = $lines[0];
        $line2 = $lines[1];

        if (strlen($line1) !== 44 || strlen($line2) !== 44) {
            return [
                'ok' => false,
                'message' => 'Detected MRZ lines are not valid passport length.',
                'raw_text' => $rawText,
                'lines' => [$line1, $line2],
            ];
        }

        $documentType = substr($line1, 0, 1);
        $issuingCountry = preg_replace('/[^A-Z]/', '', $this->cleanField(substr($line1, 2, 3))) ?? '';

        $namesBlock = substr($line1, 5, 39);
        [$surname, $givenNames] = $this->parseNames($namesBlock);

        $passportNumberRaw = substr($line2, 0, 9);
        $passportNumber = $this->cleanField($passportNumberRaw);
        $passportNumberCheck = substr($line2, 9, 1);

        $nationalityCode = preg_replace('/[^A-Z]/', '', $this->cleanField(substr($line2, 10, 3))) ?? '';
        $nationalityCode = substr($nationalityCode, 0, 3);

        $birthDateRaw = substr($line2, 13, 6);
        $birthDateCheck = substr($line2, 19, 1);

        $sex = $this->cleanField(substr($line2, 20, 1));

        $expiryDateRaw = substr($line2, 21, 6);
        $expiryDateCheck = substr($line2, 27, 1);

        $optionalData = substr($line2, 28, 14);
        $optionalDataCheck = substr($line2, 42, 1);
        $finalCheck = substr($line2, 43, 1);

        $checks = [
            'passport_number' => $this->matchesCheckDigit($passportNumberRaw, $passportNumberCheck),
            'date_of_birth' => $this->matchesCheckDigit($birthDateRaw, $birthDateCheck),
            'passport_expiry_date' => $this->matchesCheckDigit($expiryDateRaw, $expiryDateCheck),
            'optional_data' => $this->matchesCheckDigit($optionalData, $optionalDataCheck),
            'final' => $this->matchesCheckDigit(
                substr($line2, 0, 10) .
                substr($line2, 13, 7) .
                substr($line2, 21, 22),
                $finalCheck
            ),
        ];

        $passedChecks = collect($checks)->filter(fn ($v) => $v === true)->count();
        $totalChecks = count($checks);
        $confidence = round(($passedChecks / max($totalChecks, 1)) * 100, 2);

        return [
            'ok' => true,
            'document_type' => $documentType,
            'issuing_country' => $issuingCountry,
            'surname' => $surname,
            'given_names' => $givenNames,
            'passport_number' => $passportNumber,
            'nationality_code' => $nationalityCode,
            'date_of_birth' => $this->convertMrzDate($birthDateRaw, false),
            'sex' => $sex === '<' ? '' : $sex,
            'passport_expiry_date' => $this->convertMrzDate($expiryDateRaw, true),
            'raw' => [
                'line_1' => $line1,
                'line_2' => $line2,
            ],
            'checks' => $checks,
            'confidence' => $confidence,
        ];
    }

    protected function normalizeLines(string $rawText): array
    {
        $lines = preg_split('/\R+/', strtoupper($rawText)) ?: [];

        $lines = array_values(array_filter(array_map(function (string $line) {
            $line = preg_replace('/[^A-Z0-9<]/', '', $line) ?? '';
            $line = str_replace(['«'], '<', $line);

            return trim($line);
        }, $lines)));

        $candidates = array_values(array_filter($lines, function (string $line) {
            return strlen($line) >= 30 && str_contains($line, '<');
        }));

        usort($candidates, fn ($a, $b) => strlen($b) <=> strlen($a));

        if (count($candidates) >= 2) {
            return [
                str_pad(substr($candidates[0], 0, 44), 44, '<'),
                str_pad(substr($candidates[1], 0, 44), 44, '<'),
            ];
        }

        return $candidates;
    }

    protected function parseNames(string $block): array
    {
        $parts = explode('<<', $block, 2);

        $surname = $this->cleanName($parts[0] ?? '');
        $givenNames = $this->cleanName($parts[1] ?? '');

        $givenNames = preg_replace('/(?:\s+[A-Z]){2,}$/', '', $givenNames) ?? $givenNames;
        $givenNames = preg_replace('/\s+K$/', '', $givenNames) ?? $givenNames;
        $givenNames = trim($givenNames);

        return [$surname, $givenNames];
    }

    protected function cleanField(string $value): string
    {
        $value = strtoupper(trim(str_replace('<', '', $value)));
        $value = preg_replace('/[^A-Z0-9]/', '', $value) ?? $value;

        return $value;
    }

    protected function cleanName(string $value): string
    {
        $value = str_replace('<', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value) ?? '';
        $value = trim($value);
        $value = preg_replace('/(?:\s+[A-Z]){2,}$/', '', $value) ?? $value;
        $value = preg_replace('/[^A-Z\s\-]/', '', $value) ?? $value;
        $value = preg_replace('/\s+/', ' ', $value) ?? $value;

        return trim($value);
    }

    protected function convertMrzDate(string $value, bool $futureCentury): ?string
    {
        if (! preg_match('/^\d{6}$/', $value)) {
            return null;
        }

        $yy = (int) substr($value, 0, 2);
        $mm = (int) substr($value, 2, 2);
        $dd = (int) substr($value, 4, 2);

        $currentYear = (int) date('Y');
        $currentCentury = (int) floor($currentYear / 100) * 100;

        if ($futureCentury) {
            $year = $yy + $currentCentury;

            if ($year < $currentYear - 5) {
                $year += 100;
            }

            if ($year > $currentYear + 20) {
                $year -= 100;
            }
        } else {
            $year = $yy + $currentCentury;

            if ($year > $currentYear) {
                $year -= 100;
            }
        }

        if (! checkdate($mm, $dd, $year)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', $year, $mm, $dd);
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
}
