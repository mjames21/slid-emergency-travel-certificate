<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use App\Support\StaffEmailDomains;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        Fortify::authenticateUsing(function (Request $request): ?User {
            $email = strtolower((string) $request->input(Fortify::username()));

            if (! StaffEmailDomains::allows($email)) {
                return null;
            }

            $user = User::query()->where('email', $email)->first();

            if ($user && Hash::check((string) $request->input('password'), $user->password)) {
                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $username = $this->throttleValue((string) $request->input(Fortify::username()));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by("login-user-ip|{$username}|{$ip}"),
                Limit::perMinute(20)->by("login-ip|{$ip}"),
                Limit::perHour(30)->by("login-user-hour|{$username}"),
                Limit::perDay(120)->by("login-ip-day|{$ip}"),
            ];
        });

        RateLimiter::for('two-factor', function (Request $request) {
            $loginId = (string) ($request->session()->get('login.id') ?: $request->session()->getId());
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by("two-factor-user|{$loginId}"),
                Limit::perMinute(15)->by("two-factor-ip|{$ip}"),
                Limit::perHour(15)->by("two-factor-user-hour|{$loginId}"),
            ];
        });

        RateLimiter::for('passkeys', function (Request $request) {
            $credentialId = $this->throttleValue((string) $request->input('credential.id'));
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(10)->by("passkey-credential-ip|{$credentialId}|{$ip}"),
                Limit::perMinute(25)->by("passkey-ip|{$ip}"),
                Limit::perHour(50)->by("passkey-ip-hour|{$ip}"),
            ];
        });
    }

    private function throttleValue(string $value): string
    {
        $value = trim(Str::lower($value));

        if ($value === '') {
            return 'missing';
        }

        return Str::transliterate($value);
    }
}
