<?php

// FILE: app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Contracts\MrzExtractor;
use App\Services\Mrz\TesseractMrzExtractor;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MrzExtractor::class, TesseractMrzExtractor::class);
    }

    public function boot(): void
    {
        RateLimiter::for('etc-read-passport', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(10)->by('etc-read-passport-minute|'.$ip),
                Limit::perHour(40)->by('etc-read-passport-hour|'.$ip),
            ];
        });

        RateLimiter::for('etc-submit', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by('etc-submit-minute|'.$ip),
                Limit::perHour(20)->by('etc-submit-hour|'.$ip),
                Limit::perDay(50)->by('etc-submit-day|'.$ip),
            ];
        });

        RateLimiter::for('etc-status', function (Request $request) {
            $ip = (string) $request->ip();
            $token = (string) $request->route('token');

            return [
                Limit::perMinute(12)->by('etc-status-token|'.$ip.'|'.$token),
                Limit::perMinute(30)->by('etc-status-ip|'.$ip),
                Limit::perHour(120)->by('etc-status-ip-hour|'.$ip),
            ];
        });

        RateLimiter::for('permit-verify', function (Request $request) {
            $ip = (string) $request->ip();
            $code = (string) $request->route('code');

            return [
                Limit::perMinute(10)->by('permit-verify-code|'.$ip.'|'.$code),
                Limit::perMinute(30)->by('permit-verify-ip|'.$ip),
                Limit::perHour(120)->by('permit-verify-ip-hour|'.$ip),
            ];
        });

        RateLimiter::for('wangov-webhook', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(120)->by('wangov-webhook-minute|'.$ip),
                Limit::perHour(1000)->by('wangov-webhook-hour|'.$ip),
            ];
        });
    }
}
