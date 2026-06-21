<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeatureScopeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function public_scope_defaults_to_permit_and_emergency_travel_certificate(): void
    {
        config([
            'features.emergency_travel_certificate' => true,
            'features.border_management' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('National landing permit and Emergency Travel Certificate platform')
            ->assertSee('Apply for Emergency Travel Certificate')
            ->assertDontSee('Border Movements');

        $this->get('/emergency-travel-certificate/apply')->assertOk();
        $this->get('/evisa/apply')->assertRedirect('/emergency-travel-certificate/apply');
    }

    #[Test]
    public function emergency_travel_certificate_can_be_disabled_by_feature_flag(): void
    {
        config([
            'features.emergency_travel_certificate' => false,
            'features.border_management' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Apply for Emergency Travel Certificate');

        $this->get('/emergency-travel-certificate/apply')->assertNotFound();
    }
}
