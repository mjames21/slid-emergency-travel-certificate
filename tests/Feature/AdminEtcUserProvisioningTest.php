<?php

namespace Tests\Feature;

use App\Livewire\Admin\Staff\UsersIndex;
use App\Models\StaffTitle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminEtcUserProvisioningTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function system_administrator_can_create_etc_issuer_and_executive_users(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $systemAdmin = $this->staffUserWithTitle('system_administrator', 'System Administrator');
        $issuerTitle = $this->staffTitle('etc_issuer', 'ETC Issuer');
        $executiveTitle = $this->staffTitle('executive_observer', 'Executive');

        $this->actingAs($systemAdmin);

        Livewire::test(UsersIndex::class)
            ->set('name', 'ETC Issuer One')
            ->set('email', 'etc.issuer@immigration.gov.sl')
            ->set('staffNumber', 'ETC-001')
            ->set('phone', '+232700000101')
            ->set('titleCode', 'etc_issuer')
            ->set('password', 'StrongPassword123!')
            ->call('createUser')
            ->assertHasNoErrors();

        Livewire::test(UsersIndex::class)
            ->set('name', 'Executive One')
            ->set('email', 'executive@immigration.gov.sl')
            ->set('staffNumber', 'EXE-001')
            ->set('titleCode', 'executive_observer')
            ->set('password', 'StrongPassword123!')
            ->call('createUser')
            ->assertHasNoErrors();

        $issuer = User::query()->where('email', 'etc.issuer@immigration.gov.sl')->firstOrFail();
        $executive = User::query()->where('email', 'executive@immigration.gov.sl')->firstOrFail();

        $this->assertTrue(Hash::check('StrongPassword123!', $issuer->password));
        $this->assertTrue($issuer->hasStaffTitle('etc_issuer'));
        $this->assertTrue($executive->hasStaffTitle('executive_observer'));
        $this->assertDatabaseHas('user_staff_titles', [
            'user_id' => $issuer->id,
            'staff_title_id' => $issuerTitle->id,
            'assigned_by_user_id' => $systemAdmin->id,
        ]);
        $this->assertDatabaseHas('user_staff_titles', [
            'user_id' => $executive->id,
            'staff_title_id' => $executiveTitle->id,
            'assigned_by_user_id' => $systemAdmin->id,
        ]);
    }

    #[Test]
    public function system_administrator_cannot_create_etc_user_with_non_staff_email(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $systemAdmin = $this->staffUserWithTitle('system_administrator', 'System Administrator');
        $this->staffTitle('etc_issuer', 'ETC Issuer');

        $this->actingAs($systemAdmin);

        Livewire::test(UsersIndex::class)
            ->set('name', 'External User')
            ->set('email', 'external@example.com')
            ->set('titleCode', 'etc_issuer')
            ->set('password', 'StrongPassword123!')
            ->call('createUser')
            ->assertHasErrors(['email']);

        $this->assertDatabaseMissing('users', [
            'email' => 'external@example.com',
        ]);
    }

    #[Test]
    public function system_administrator_receives_one_time_login_details_after_creating_user(): void
    {
        config(['security.staff_email_domains' => ['immigration.gov.sl']]);

        $systemAdmin = $this->staffUserWithTitle('system_administrator', 'System Administrator');
        $this->staffTitle('etc_issuer', 'ETC Issuer');

        $this->actingAs($systemAdmin);

        Livewire::test(UsersIndex::class)
            ->set('name', 'ETC Issuer One')
            ->set('email', 'etc.issuer@immigration.gov.sl')
            ->set('staffNumber', 'ETC-001')
            ->set('titleCode', 'etc_issuer')
            ->set('password', 'StrongPassword123!')
            ->call('createUser')
            ->assertSet('showLoginDetails', true)
            ->assertSet('loginDetails.email', 'etc.issuer@immigration.gov.sl')
            ->assertSet('loginDetails.role', 'ETC Issuer')
            ->assertSet('loginDetails.password', 'StrongPassword123!')
            ->assertSee('Share Staff Login Details')
            ->assertSee('StrongPassword123!')
            ->assertSee(route('login'))
            ->call('closeLoginDetails')
            ->assertSet('showLoginDetails', false)
            ->assertSet('loginDetails', [])
            ->assertDontSee('StrongPassword123!');
    }

    #[Test]
    public function non_system_administrator_cannot_access_etc_user_provisioning(): void
    {
        $this->staffTitle('system_administrator', 'System Administrator');
        $issuer = $this->staffUserWithTitle('etc_issuer', 'ETC Issuer');

        $this->actingAs($issuer)
            ->get(route('admin.staff.users.index'))
            ->assertForbidden();
    }

    private function staffUserWithTitle(string $code, string $name): User
    {
        $title = $this->staffTitle($code, $name);

        $user = User::factory()->create(['active' => true]);

        $user->staffTitles()->attach($title->id, [
            'assigned_at' => now(),
            'is_primary' => true,
        ]);

        return $user->fresh(['staffTitles']);
    }

    private function staffTitle(string $code, string $name): StaffTitle
    {
        return StaffTitle::query()->firstOrCreate(
            ['code' => $code],
            [
                'name' => $name,
                'description' => "{$name} test role",
                'active' => true,
            ]
        );
    }
}
