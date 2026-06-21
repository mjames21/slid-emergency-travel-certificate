<?php

namespace Tests\Unit;

use App\Models\Permit;
use App\Models\TravelRequirementRule;
use App\Models\User;
use App\Models\VisaApplication;
use App\Services\Border\AdmissibilityScreeningService;
use App\Support\MrzGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissibilityScreeningServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_records_a_clear_low_risk_screening_for_a_valid_permit(): void
    {
        $officer = User::factory()->create();

        $application = VisaApplication::factory()->create();
        TravelRequirementRule::create([
            'source' => 'sl_immigration',
            'nationality_code' => $application->passenger->nationality_code,
            'visa_type' => 'visa_on_arrival',
            'max_stay_days' => 30,
            'min_passport_validity_days' => 0,
            'visa_required' => true,
            'host_address_required' => true,
            'active' => true,
        ]);

        $application->passenger->update([
            'passport_expiry_date' => now()->addYear()->toDateString(),
            'passport_mrz_data' => [
                'checks' => [
                    'passport_number' => true,
                    'date_of_birth' => true,
                    'passport_expiry_date' => true,
                    'optional_data' => true,
                    'final' => true,
                ],
            ],
        ]);

        $permit = Permit::factory()
            ->for($application, 'visaApplication')
            ->create([
                'payment_id' => null,
                'valid_until' => now()->addMonth()->toDateString(),
                'permit_status' => 'active',
            ]);

        $mrz = app(MrzGenerator::class)->generate($permit->fresh(['visaApplication.passenger']));
        $permit->update([
            'mrz_type' => $mrz['type'],
            'mrz_line_1' => $mrz['line_1'],
            'mrz_line_2' => $mrz['line_2'],
        ]);

        $screening = app(AdmissibilityScreeningService::class)
            ->screenPermit($permit->fresh(['visaApplication.passenger']), $officer);

        $this->assertSame('clear', $screening->status);
        $this->assertSame('low', $screening->risk_level);
        $this->assertTrue($screening->passport_valid);
        $this->assertTrue($screening->permit_valid);
        $this->assertTrue($screening->mrz_verified);
        $this->assertDatabaseHas('admissibility_screenings', [
            'id' => $screening->id,
            'permit_id' => $permit->id,
            'screened_by' => $officer->id,
        ]);
    }
}
