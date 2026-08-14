<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\StaffTitleSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class SeededStaffAccountsTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_seeder_creates_verified_active_staff_accounts_with_titles(): void
    {
        config([
            'security.staff_email_domains' => ['immigration.gov.sl'],
            'seeded_staff.allow_default_passwords' => false,
            'seeded_staff.users' => $this->seedUsers(),
        ]);

        $this->seed(StaffTitleSeeder::class);
        $this->seed(UserSeeder::class);

        foreach ($this->seedUsers() as $account) {
            $user = User::query()
                ->where('email', $account['email'])
                ->with('staffTitles')
                ->firstOrFail();

            $this->assertTrue($user->active);
            $this->assertNotNull($user->email_verified_at);
            $this->assertTrue(Hash::check($account['password'], $user->password));
            $this->assertTrue($user->hasStaffTitle($account['title_code']));
        }
    }

    #[Test]
    public function production_seed_requires_password_for_new_staff_account(): void
    {
        config([
            'security.staff_email_domains' => ['immigration.gov.sl'],
            'seeded_staff.allow_default_passwords' => false,
            'seeded_staff.users' => [
                'system_administrator' => array_replace(
                    $this->seedUsers()['system_administrator'],
                    ['password' => null]
                ),
            ],
        ]);

        $this->seed(StaffTitleSeeder::class);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Set SEED_SYSTEM_ADMIN_PASSWORD');

        $this->seed(UserSeeder::class);
    }

    #[Test]
    public function user_seeder_does_not_reset_existing_password_when_seed_password_is_blank(): void
    {
        config([
            'security.staff_email_domains' => ['immigration.gov.sl'],
            'seeded_staff.allow_default_passwords' => false,
            'seeded_staff.users' => [
                'system_administrator' => array_replace(
                    $this->seedUsers()['system_administrator'],
                    ['password' => null]
                ),
            ],
        ]);

        $this->seed(StaffTitleSeeder::class);

        $existingPassword = 'ExistingStrongPassword123!';

        User::factory()->create([
            'email' => 'prod.admin@immigration.gov.sl',
            'password' => Hash::make($existingPassword),
            'active' => false,
            'email_verified_at' => null,
        ]);

        $this->seed(UserSeeder::class);

        $user = User::query()
            ->where('email', 'prod.admin@immigration.gov.sl')
            ->with('staffTitles')
            ->firstOrFail();

        $this->assertTrue(Hash::check($existingPassword, $user->password));
        $this->assertTrue($user->active);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->hasStaffTitle('system_administrator'));
    }

    /**
     * @return array<string, array<string, string|null>>
     */
    private function seedUsers(): array
    {
        return [
            'system_administrator' => [
                'title_code' => 'system_administrator',
                'name' => 'Production Admin',
                'email' => 'prod.admin@immigration.gov.sl',
                'staff_number' => 'PROD-ADMIN-001',
                'job_title' => 'System Administrator',
                'phone' => '+232700000001',
                'password' => 'AdminSeedPassword123!',
                'password_env' => 'SEED_SYSTEM_ADMIN_PASSWORD',
            ],
            'etc_issuer' => [
                'title_code' => 'etc_issuer',
                'name' => 'Production ETC Issuer',
                'email' => 'prod.issuer@immigration.gov.sl',
                'staff_number' => 'PROD-ETC-001',
                'job_title' => 'ETC Issuer',
                'phone' => '+232700000002',
                'password' => 'IssuerSeedPassword123!',
                'password_env' => 'SEED_ETC_ISSUER_PASSWORD',
            ],
            'executive_observer' => [
                'title_code' => 'executive_observer',
                'name' => 'Production Executive',
                'email' => 'prod.executive@immigration.gov.sl',
                'staff_number' => 'PROD-EXE-001',
                'job_title' => 'Executive',
                'phone' => '+232700000003',
                'password' => 'ExecutiveSeedPassword123!',
                'password_env' => 'SEED_EXECUTIVE_PASSWORD',
            ],
        ];
    }
}
