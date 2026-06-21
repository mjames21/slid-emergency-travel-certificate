<?php
// FILE: app/Services/Mrz/TesseractMrzExtractor.php

namespace App\Services\Mrz;

use App\Contracts\MrzExtractor;
use RuntimeException;

class TesseractMrzExtractor implements MrzExtractor
{
    public function extract(string $absoluteImagePath): array
    {
        if (! file_exists($absoluteImagePath)) {
            throw new RuntimeException('Passport image file not found.');
        }

        $attempts = [
            ['psm' => 7, 'lang' => 'eng'],
            ['psm' => 6, 'lang' => 'eng'],
            ['psm' => 13, 'lang' => 'eng'],
        ];

        $bestText = '';
        $bestScore = -1;

        foreach ($attempts as $attempt) {
            $text = $this->runTesseract($absoluteImagePath, $attempt['psm'], $attempt['lang']);
            $score = $this->scoreMrzLikeText($text);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestText = $text;
            }

            if ($this->looksLikeMrz($text)) {
                return [
                    'text' => $text,
                    'confidence' => null,
                ];
            }
        }

        return [
            'text' => $bestText,
            'confidence' => null,
        ];
    }

    protected function runTesseract(string $absoluteImagePath, int $psm, string $lang): string
    {
        $this->validateLanguage($lang);

        $tmpBase = tempnam(sys_get_temp_dir(), 'mrz_');

        if ($tmpBase === false) {
            throw new RuntimeException('Unable to allocate temporary file for MRZ extraction.');
        }

        @unlink($tmpBase);
        $tmpBase = $tmpBase . '_ocr';

        $exitCode = $this->runProcess([
            $this->tesseractBinary(),
            $absoluteImagePath,
            $tmpBase,
            '--oem',
            '1',
            '--psm',
            (string) $psm,
            '-l',
            $lang,
        ]);

        $txtPath = $tmpBase . '.txt';

        if ($exitCode !== 0 || ! file_exists($txtPath)) {
            return '';
        }

        $text = file_get_contents($txtPath) ?: '';

        @unlink($txtPath);

        return $this->normalizeOcrText($text);
    }

    protected function runProcess(array $command): int
    {
        $pipes = [];
        $process = @proc_open($command, [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);

        if (! is_resource($process)) {
            return 1;
        }

        foreach ($pipes as $pipe) {
            if (is_resource($pipe)) {
                stream_get_contents($pipe);
                fclose($pipe);
            }
        }

        return proc_close($process);
    }

    protected function tesseractBinary(): string
    {
        $binary = trim((string) config('services.tesseract.binary', 'tesseract'));

        if ($binary === '' || preg_match('/[\x00-\x1F\x7F]/', $binary) === 1) {
            throw new RuntimeException('Tesseract executable path is invalid.');
        }

        if (str_contains($binary, DIRECTORY_SEPARATOR) && ! is_executable($binary)) {
            throw new RuntimeException('Tesseract executable path is not executable.');
        }

        return $binary;
    }

    protected function validateLanguage(string $lang): void
    {
        if (preg_match('/^[A-Za-z0-9_+-]+$/', $lang) !== 1) {
            throw new RuntimeException('Tesseract language code is invalid.');
        }
    }

    protected function normalizeOcrText(string $text): string
    {
        $lines = preg_split('/\R+/', strtoupper($text)) ?: [];

        $lines = array_map(function (string $line) {
            $line = trim($line);
            $line = str_replace(['«', ' '], ['<', ''], $line);
            $line = preg_replace('/[^A-Z0-9<]/', '', $line) ?? '';

            $line = preg_replace('/(?<=[A-Z])0(?=[A-Z<])/', 'O', $line) ?? $line;
            $line = preg_replace('/(?<=[0-9])O(?=[0-9<])/', '0', $line) ?? $line;

            return $line;
        }, $lines);

        $lines = array_values(array_filter($lines));

        return implode("\n", $lines);
    }

    protected function looksLikeMrz(string $text): bool
    {
        $lines = preg_split('/\R+/', strtoupper($text)) ?: [];

        $mrzLike = array_values(array_filter($lines, function (string $line) {
            $clean = preg_replace('/[^A-Z0-9<]/', '', $line) ?? '';

            return strlen($clean) >= 30 && str_contains($clean, '<');
        }));

        return count($mrzLike) >= 2;
    }

    protected function scoreMrzLikeText(string $text): int
    {
        $lines = preg_split('/\R+/', strtoupper($text)) ?: [];
        $score = 0;

        foreach ($lines as $line) {
            $clean = preg_replace('/[^A-Z0-9<]/', '', $line) ?? '';

            if (strlen($clean) >= 30) {
                $score += 10;
            }

            if (str_contains($clean, '<')) {
                $score += 20;
            }

            if (preg_match('/^[A-Z0-9<]+$/', $clean)) {
                $score += 10;
            }

            if (strlen($clean) >= 40) {
                $score += 20;
            }
        }

        return $score;
    }
}
