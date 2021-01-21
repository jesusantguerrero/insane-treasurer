<?php

use Illuminate\Support\Facades\Route;
use Insane\Paypal\Http\Controllers\PlansController;
use Insane\Paypal\Http\Controllers\SubscriptionsController;

// resource route
Route::middleware(config('jetstream.middleware', ['web']))->group(function() {
    Route::get('/subscribe/paypal/return', [SubscriptionsController::class, 'paypalReturn'])->name('paypal.return');
    Route::get('/subscriptions/paypal-return', [SubscriptionsController::class, 'paypalReturn'])->name('paypal.return');
    Route::get('/subscriptions/{planId}/subscribe', [SubscriptionsController::class, 'paypalSubscribe'])->name('paypal.subscribe');

    Route::get('/subscriptions/{id}/agreements/${agreementId}', [SubscriptionsController::class, 'paypalAgreement'])->name('paypal.agreement');
    Route::post('/subscriptions/{id}/agreement/{agreementId}/cancel', [SubscriptionsController::class, 'paypalCancel'])->name('paypal.cancel');
    Route::post('/subscriptions/{id}/agreement/{agreementId}/reactivate', [SubscriptionsController::class, 'paypalReactivate'])->name('paypal.reactivate');
    Route::post('/subscriptions/{id}/agreement/{agreementId}/suspend', [SubscriptionsController::class, 'paypalSuspend'])->name('paypal.suspend');
    Route::get('/subscriptions/{id}/agreement/{agreementId}/suspend', [SubscriptionsController::class, 'paypalSuspend'])->name('paypal.suspend');

    Route::resource('/plans', PlansController::class);
});

Route::middleware([])->prefix("api/v1")->group(function () {
    Route::post('/plans', [PlansController::class, 'store']);
});
