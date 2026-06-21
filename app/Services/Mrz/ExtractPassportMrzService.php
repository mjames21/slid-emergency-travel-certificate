<?php
// FILE: app/Services/Mrz/ExtractPassportMrzService.php

namespace App\Services\Mrz;

use App\Contracts\MrzExtractor;
use RuntimeException;

class ExtractPassportMrzService
{
    public function __construct(
        protected MrzExtractor $extractor,
        protected MrzParser $parser
    ) {
    }

    public function handle(string $absoluteImagePath): array
    {
        $ocr = $this->extractor->extract($absoluteImagePath);
        $parsed = $this->parser->parsePassport($ocr['text'] ?? '');

        if (! ($parsed['ok'] ?? false)) {
            throw new RuntimeException($parsed['message'] ?? 'MRZ could not be read from the passport image.');
        }

        return [
            'raw' => $parsed['raw'] ?? [],
            'parsed' => [
                'document_type' => $parsed['document_type'] ?? null,
                'issuing_country' => $parsed['issuing_country'] ?? null,
                'surname' => $parsed['surname'] ?? null,
                'given_names' => $parsed['given_names'] ?? null,
                'passport_number' => $parsed['passport_number'] ?? null,
                'nationality_code' => $parsed['nationality_code'] ?? null,
                'date_of_birth' => $parsed['date_of_birth'] ?? null,
                'sex' => $parsed['sex'] ?? null,
                'passport_expiry_date' => $parsed['passport_expiry_date'] ?? null,
            ],
            'checks' => $parsed['checks'] ?? [],
            'confidence' => $parsed['confidence'] ?? null,
            'ocr_text' => $ocr['text'] ?? '',
        ];
    }
}