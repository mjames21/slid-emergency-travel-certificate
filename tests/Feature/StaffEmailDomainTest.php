<?php

namespace Tests\Feature;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Laravel\Jetstream\Jetstream;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StaffEmailDomainTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function fortify_user_creation_accepts_staff_email_domains(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        app(CreateNewUser::class)->create([
            'name' => 'Staff User',
            'email' => 'STAFF.USER@IMMIGRATION.GOV.SL',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'staff.user@immigration.gov.sl',
        ]);
    }

    #[Test]
    public function fortify_user_creation_rejects_non_staff_email_domains(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $this->expectException(ValidationException::class);

        app(CreateNewUser::class)->create([
            'name' => 'External User',
            'email' => 'external@example.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
        ]);
    }
}
