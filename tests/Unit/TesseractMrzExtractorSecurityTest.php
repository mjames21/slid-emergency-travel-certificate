<?php

namespace Tests\Unit;

use App\Services\Mrz\TesseractMrzExtractor;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class TesseractMrzExtractorSecurityTest extends TestCase
{
    #[Test]
    public function it_rejects_invalid_tesseract_language_values(): void
    {
        $this->expectException(RuntimeException::class);

        $this->extractor()->validatePublicLanguage('eng;rm -rf /');
    }

    #[Test]
    public function it_rejects_control_characters_in_tesseract_binary_config(): void
    {
        config(['services.tesseract.binary' => "tesseract\n--bad"]);

        $this->expectException(RuntimeException::class);

        $this->extractor()->publicBinary();
    }

    private function extractor(): object
    {
        return new class extends TesseractMrzExtractor {
            public function validatePublicLanguage(string $lang): void
            {
                $this->validateLanguage($lang);
            }

            public function publicBinary(): string
            {
                return $this->tesseractBinary();
            }
        };
    }
}
