<?php
// FILE: app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Contracts\MrzExtractor;
use App\Models\Permit;
use App\Services\Mrz\TesseractMrzExtractor;
use App\Support\PermitLifecycleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
                Limit::perMinute(10)->by('etc-read-passport-minute|' . $ip),
                Limit::perHour(40)->by('etc-read-passport-hour|' . $ip),
            ];
        });

        RateLimiter::for('etc-submit', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(5)->by('etc-submit-minute|' . $ip),
                Limit::perHour(20)->by('etc-submit-hour|' . $ip),
                Limit::perDay(50)->by('etc-submit-day|' . $ip),
            ];
        });

        RateLimiter::for('etc-status', function (Request $request) {
            $ip = (string) $request->ip();
            $token = (string) $request->route('token');

            return [
                Limit::perMinute(12)->by('etc-status-token|' . $ip . '|' . $token),
                Limit::perMinute(30)->by('etc-status-ip|' . $ip),
                Limit::perHour(120)->by('etc-status-ip-hour|' . $ip),
            ];
        });

        RateLimiter::for('permit-verify', function (Request $request) {
            $ip = (string) $request->ip();
            $code = (string) $request->route('code');

            return [
                Limit::perMinute(10)->by('permit-verify-code|' . $ip . '|' . $code),
                Limit::perMinute(30)->by('permit-verify-ip|' . $ip),
                Limit::perHour(120)->by('permit-verify-ip-hour|' . $ip),
            ];
        });

        RateLimiter::for('wangov-webhook', function (Request $request) {
            $ip = (string) $request->ip();

            return [
                Limit::perMinute(120)->by('wangov-webhook-minute|' . $ip),
                Limit::perHour(1000)->by('wangov-webhook-hour|' . $ip),
            ];
        });

        View::composer('components.layouts.app', function ($view): void {
            $user = auth()->user();

            $isSystemAdmin = $user && $user->hasStaffTitle('system_administrator');
            $isHqAdmin = $user && $user->hasStaffTitle('hq_administrator');
            $isAirportManager = $user && $user->hasStaffTitle('airport_manager');
            $isShiftSupervisor = $user && $user->hasStaffTitle('shift_supervisor');
            $isVisaOfficer = $user && $user->hasStaffTitle('visa_processing_officer');
            $isPaymentOfficer = $user && $user->hasStaffTitle('payment_officer');
            $isAuditor = $user && $user->hasStaffTitle('compliance_auditor');
            $isExecutive = $user && $user->hasStaffTitle('executive_observer');

            $canSeeStaffExpiryReport = $isSystemAdmin || $isAirportManager || $isShiftSupervisor || $isVisaOfficer || $isPaymentOfficer;
            $canSeeHqExpiryCounts = $isSystemAdmin || $isHqAdmin || $isAuditor || $isExecutive;

            $staffExpiringSoonCount = 0;
            $staffExpiredCount = 0;
            $hqExpiringSoonCount = 0;
            $hqExpiredCount = 0;

            $hasPermitTable = Schema::hasTable('permits');

            if ($canSeeStaffExpiryReport && $hasPermitTable) {
                $staffQuery = Permit::query()
                    ->whereNotNull('valid_until');

                PermitLifecycleStatus::constrainActive($staffQuery);

                if ($user && ! $isSystemAdmin && $user->primaryAirport?->id) {
                    $staffQuery->whereHas('visaApplication', function (Builder $query) use ($user) {
                        $query->where('airport_id', $user->primaryAirport->id);
                    });
                }

                $staffExpiringSoonCount = (clone $staffQuery)
                    ->whereDate('valid_until', '>=', today())
                    ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
                    ->count();

                $staffExpiredCount = (clone $staffQuery)
                    ->whereDate('valid_until', '<', today())
                    ->count();
            }

            if ($canSeeHqExpiryCounts && $hasPermitTable) {
                $hqQuery = Permit::query()
                    ->whereNotNull('valid_until');

                PermitLifecycleStatus::constrainActive($hqQuery);

                $hqExpiringSoonCount = (clone $hqQuery)
                    ->whereDate('valid_until', '>=', today())
                    ->whereDate('valid_until', '<=', today()->copy()->addDays(7))
                    ->count();

                $hqExpiredCount = (clone $hqQuery)
                    ->whereDate('valid_until', '<', today())
                    ->count();
            }

            $view->with([
                'canSeeStaffExpiryReport' => $canSeeStaffExpiryReport,
                'staffExpiringSoonCount' => $staffExpiringSoonCount,
                'staffExpiredCount' => $staffExpiredCount,
                'canSeeHqExpiryCounts' => $canSeeHqExpiryCounts,
                'hqExpiringSoonCount' => $hqExpiringSoonCount,
                'hqExpiredCount' => $hqExpiredCount,
            ]);
        });
    }
}
