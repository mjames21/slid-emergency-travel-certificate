<?php

// FILE: routes/web.php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvisaApplicationController;
use App\Http\Controllers\VerifyPermitController;
use App\Http\Controllers\Webhooks\WangovPaymentUpdateWebhookController;
use App\Livewire\Admin\Staff\UsersIndex as AdminStaffUsersIndex;
use App\Livewire\Hq\EvisaApplications\Index as HqEvisaApplicationsIndex;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/emergency-travel-certificate/apply')->name('home');

Route::get('/emergency-travel-certificate/apply', [EvisaApplicationController::class, 'create'])
    ->name('etc.apply');

Route::post('/emergency-travel-certificate/apply', [EvisaApplicationController::class, 'store'])
    ->middleware('throttle:etc-submit')
    ->name('etc.store');

Route::post('/emergency-travel-certificate/read-passport', [EvisaApplicationController::class, 'readPassport'])
    ->middleware('throttle:etc-read-passport')
    ->name('etc.read-passport');

Route::get('/emergency-travel-certificate/status/{token}', [EvisaApplicationController::class, 'status'])
    ->middleware('throttle:etc-status')
    ->name('etc.status');

Route::post('/emergency-travel-certificate/pay/{token}', [EvisaApplicationController::class, 'pay'])
    ->middleware('throttle:etc-status')
    ->name('etc.pay');

Route::post('/webhooks/wangov', WangovPaymentUpdateWebhookController::class)
    ->middleware('throttle:wangov-webhook')
    ->name('webhooks.wangov');

Route::post('/api/wangov/payment-update', WangovPaymentUpdateWebhookController::class)
    ->middleware('throttle:wangov-webhook')
    ->name('api.wangov.payment_update');

Route::get('/verify/{code}', VerifyPermitController::class)
    ->middleware(['verified.permit.access', 'throttle:permit-verify'])
    ->name('verify.permit');

Route::middleware(['auth', 'verified', 'active', 'staff.access', 'staff.mfa'])
    ->group(function () {
        Route::redirect('/dashboard', '/hq/emergency-travel-certificates')
            ->middleware('staff.title:system_administrator,etc_issuer,executive_observer')
            ->name('dashboard');

        Route::prefix('hq')
            ->name('hq.')
            ->middleware('staff.title:system_administrator,etc_issuer,executive_observer')
            ->group(function () {
                Route::get('/emergency-travel-certificates', HqEvisaApplicationsIndex::class)
                    ->name('emergency-travel-certificates.index');
            });

        Route::prefix('admin')
            ->name('admin.')
            ->middleware('staff.title:system_administrator')
            ->group(function () {
                Route::get('/staff/users', AdminStaffUsersIndex::class)
                    ->name('staff.users.index');
            });

        Route::get('/documents/certificates/{permit}', [DocumentController::class, 'permit'])
            ->middleware([
                'staff.title:system_administrator,etc_issuer,executive_observer',
            ])
            ->name('documents.certificates.show');
    });
