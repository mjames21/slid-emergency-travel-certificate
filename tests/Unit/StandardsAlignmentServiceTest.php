<?php

namespace Tests\Unit;

use App\Services\Standards\StandardsAlignmentService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StandardsAlignmentServiceTest extends TestCase
{
    #[Test]
    public function it_scores_operational_readiness_across_icao_iata_and_iom_sections(): void
    {
        $readiness = app(StandardsAlignmentService::class)->forForm([
            'passport_biodata_available' => true,
            'passport_mrz_raw_text' => 'P<SLEJAMES<<MOHAMED<<<<<<<<<<<<<<<<<<<<<<<<',
            'passport_mrz_result' => [
                'checks' => [
                    'passport_number' => true,
                    'date_of_birth' => true,
                    'passport_expiry_date' => true,
                    'optional_data' => true,
                    'final' => true,
                ],
            ],
            'nationality_code' => 'SLE',
            'passport_expiry_date' => '2029-03-12',
            'date_of_birth' => '1986-04-21',
            'passport_number' => 'SLR092377',
            'arrival_date' => '2026-06-14',
            'valid_from' => '2026-06-14',
            'period_of_stay_days' => '30',
            'point_of_entry' => 'Freetown International Airport',
            'flight_carrier' => 'Kenya Airways',
            'flight_number' => 'KQ510',
            'country_of_birth' => 'Sierra Leone',
            'country_of_residence' => 'Sierra Leone',
            'phone' => '+232700000000',
            'host_name' => 'SLID',
            'destination_address' => 'Freetown',
            'remarks' => 'No assistance needs observed at intake.',
        ], [
            'found' => false,
        ]);

        $this->assertCount(3, $readiness['sections']);
        $this->assertSame(0, $readiness['summary']['fail']);
        $this->assertSame(1, $readiness['summary']['warn']);
        $this->assertGreaterThanOrEqual(90, $readiness['summary']['score']);
    }
}
