<?php

namespace Tests\Unit;

use App\Models\TravelRequirementRule;
use App\Models\VisaApplication;
use App\Services\Border\TravelRequirementRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelRequirementRuleServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_matches_iata_carrier_rules_when_application_stores_carrier_name(): void
    {
        $application = VisaApplication::factory()->create([
            'flight_carrier' => 'Kenya Airways',
            'period_of_stay_days' => 14,
            'host_address' => 'Freetown',
            'destination_address' => 'Freetown',
        ]);

        TravelRequirementRule::create([
            'source' => 'sl_immigration',
            'nationality_code' => null,
            'visa_type' => 'visa_on_arrival',
            'purpose_of_visit' => null,
            'carrier_code' => 'KQ',
            'document_type' => 'passport',
            'max_stay_days' => 30,
            'min_passport_validity_days' => 0,
            'visa_required' => true,
            'return_ticket_required' => false,
            'host_address_required' => true,
            'active' => true,
            'effective_from' => now()->subDay()->toDateString(),
        ]);

        $result = app(TravelRequirementRuleService::class)->evaluate($application);

        $this->assertSame('pass', $result['status']);
        $this->assertNotNull($result['matched_rule_id']);
    }
}
