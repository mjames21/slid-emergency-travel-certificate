<?php

namespace Database\Seeders;

use App\Models\StaffTitle;
use App\Models\User;
use App\Support\StaffEmailDomains;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ((array) config('seeded_staff.users', []) as $account) {
            $this->seedStaffUser($account);
        }
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function seedStaffUser(array $account): void
    {
        $email = strtolower(trim((string) ($account['email'] ?? '')));
        $titleCode = trim((string) ($account['title_code'] ?? ''));

        if ($email === '' || $titleCode === '') {
            throw new RuntimeException('Seeded staff users require both email and title_code.');
        }

        if (! StaffEmailDomains::allows($email)) {
            throw new RuntimeException("Seeded staff email [{$email}] is outside SECURITY_STAFF_EMAIL_DOMAINS.");
        }

        $title = StaffTitle::query()
            ->where('code', $titleCode)
            ->where('active', true)
            ->firstOrFail();

        $user = User::query()->firstOrNew(['email' => $email]);
        $password = $this->passwordFor($account, $user->exists);

        $attributes = [
            'name' => (string) ($account['name'] ?? $title->name),
            'email' => $email,
            'staff_number' => $account['staff_number'] ?? null,
            'job_title' => $account['job_title'] ?? $title->name,
            'phone' => $account['phone'] ?? null,
            'active' => true,
            'email_verified_at' => now(),
        ];

        if ($password !== null) {
            $attributes['password'] = Hash::make($password);
        }

        $user->forceFill($attributes)->save();

        $user->staffTitles()->syncWithoutDetaching([
            $title->id => [
                'assigned_by_user_id' => $user->id,
                'assigned_at' => now(),
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $account
     */
    private function passwordFor(array $account, bool $userExists): ?string
    {
        $configured = trim((string) ($account['password'] ?? ''));

        if ($configured !== '') {
            return $configured;
        }

        if ($userExists) {
            return null;
        }

        if ((bool) config('seeded_staff.allow_default_passwords')) {
            return (string) config('seeded_staff.default_password');
        }

        $envName = (string) ($account['password_env'] ?? 'the matching SEED_*_PASSWORD variable');

        throw new RuntimeException("Set {$envName} before seeding this staff account in production.");
    }
}
