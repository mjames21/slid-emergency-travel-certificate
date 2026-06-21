<?php

namespace Tests\Unit;

use App\Services\Mrz\MrzParser;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PassportMrzParserTest extends TestCase
{
    #[Test]
    public function it_parses_a_doc_9303_style_td3_passport_sample(): void
    {
        $mrz = implode("\n", [
            'P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<',
            'L898902C36UTO7408122F1204159ZE184226B<<<<<10',
        ]);

        $result = app(MrzParser::class)->parsePassport($mrz);

        $this->assertTrue($result['ok']);
        $this->assertSame('P', $result['document_type']);
        $this->assertSame('UTO', $result['issuing_country']);
        $this->assertSame('ERIKSSON', $result['surname']);
        $this->assertSame('ANNA MARIA', $result['given_names']);
        $this->assertSame('L898902C3', $result['passport_number']);
        $this->assertSame('UTO', $result['nationality_code']);
        $this->assertSame('1974-08-12', $result['date_of_birth']);
        $this->assertSame('F', $result['sex']);
        $this->assertSame('2012-04-15', $result['passport_expiry_date']);
        $this->assertSame(100.0, $result['confidence']);
        $this->assertTrue(collect($result['checks'])->every(fn ($passed) => $passed === true));
    }

    #[Test]
    public function it_reports_invalid_passport_mrz_check_digits(): void
    {
        $mrz = implode("\n", [
            'P<UTOERIKSSON<<ANNA<MARIA<<<<<<<<<<<<<<<<<<<',
            'L898902C30UTO7408122F1204159ZE184226B<<<<<10',
        ]);

        $result = app(MrzParser::class)->parsePassport($mrz);

        $this->assertTrue($result['ok']);
        $this->assertFalse($result['checks']['passport_number']);
        $this->assertLessThan(100, $result['confidence']);
    }

    #[Test]
    public function it_trims_a_trailing_ocr_filler_k_from_given_names(): void
    {
        $mrz = implode("\n", [
            str_pad('P<SLEJAMES<<MOHAMED<K', 44, '<'),
            str_pad('SLR0923770SLE8604217M2903124', 44, '<'),
        ]);

        $result = app(MrzParser::class)->parsePassport($mrz);

        $this->assertTrue($result['ok']);
        $this->assertSame('JAMES', $result['surname']);
        $this->assertSame('MOHAMED', $result['given_names']);
    }
}
