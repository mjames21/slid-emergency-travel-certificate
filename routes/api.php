<?php

use App\Http\Controllers\Webhooks\WangovPaymentUpdateWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Keep this file safe to enable later. The application currently registers
| the WanGov-compatible /api/wangov/payment-update path from web.php so it
| can run without the API route group. If API routing is enabled in
| bootstrap/app.php, this route remains valid.
|
*/

Route::post('/wangov/payment-update', WangovPaymentUpdateWebhookController::class)
    ->middleware('throttle:wangov-webhook')
    ->name('api.wangov.payment_update');
