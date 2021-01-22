<?php

use Illuminate\Support\Facades\Route;
use Insane\Treasurer\Http\Controllers\PlansController;
use Insane\Treasurer\Http\Controllers\SubscriptionsController;
use Insane\Treasurer\Http\Controllers\V2\PlansController as V2PlansController;
use Insane\Treasurer\Http\Controllers\V2\ProductsController as V2ProductsController;
use Insane\Treasurer\Http\Controllers\V2\SubscriptionsController as V2SubscriptionsController;

// resource route
Route::middleware(config('jetstream.middleware', ['web']))->group(function() {
    Route::get('/subscribe/paypal/return', [SubscriptionsController::class, 'paypalReturn'])->name('paypal.return');
    Route::get('/subscriptions/paypal-return', [SubscriptionsController::class, 'paypalReturn'])->name('paypal.return');
    Route::get('/subscriptions/{planId}/subscribe', [SubscriptionsController::class, 'paypalSubscribe'])->name('paypal.subscribe');


    Route::get('/subscriptions/{id}/agreements/${agreementId}', [SubscriptionsController::class, 'paypalAgreement'])->name('paypal.agreement');
    Route::post('/subscriptions/{id}/agreement/{agreementId}/cancel', [SubscriptionsController::class, 'paypalCancel'])->name('paypal.cancel');
    Route::post('/subscriptions/{id}/agreement/{agreementId}/reactivate', [SubscriptionsController::class, 'paypalReactivate'])->name('paypal.reactivate');
    Route::post('/subscriptions/{id}/agreement/{agreementId}/suspend', [SubscriptionsController::class, 'paypalSuspend'])->name('paypal.suspend');

    // Support paypal v2
    Route::get('/v2/subscriptions/return', [V2SubscriptionsController::class, 'return'])->name('paypal.return.2');
    Route::get('/v2/subscriptions/{planId}/subscribe', [V2SubscriptionsController::class, 'subscribe'])->name('paypal.subscribe.2');
    Route::post('/v2/subscriptions/{subscriptionId}/save', [V2SubscriptionsController::class, 'save'])->name('paypal.save.2');

    Route::post('/v2/subscriptions/{id}/agreement/{agreementId}/cancel', [V2SubscriptionsController::class, 'paypalCancel'])->name('paypal.cancel.2');
    Route::post('/v2/subscriptions/{id}/agreement/{agreementId}/reactivate', [V2SubscriptionsController::class, 'paypalReactivate'])->name('paypal.reactivate.2');
    Route::post('/v2/subscriptions/{id}/agreement/{agreementId}/suspend', [V2SubscriptionsController::class, 'paypalSuspend'])->name('paypal.suspend.2');

    Route::resource('/plans', PlansController::class);
});

Route::middleware([])->prefix("api/v1")->group(function () {
    Route::post('/plans', [PlansController::class, 'store']);
});

Route::middleware([])->prefix("api/v2")->group(function () {
    Route::get('/products', [V2ProductsController::class, 'index']);
    Route::post('/products', [V2ProductsController::class, 'store']);

    Route::get('/plans', [V2PlansController::class, 'index']);
    Route::get('/plans/{id}', [V2PlansController::class, 'index']);
    Route::post('/plans', [V2PlansController::class, 'store']);

    Route::get('/subscriptions/{id}', [V2SubscriptionsController::class, 'index']);
    Route::post('/subscriptions', [V2SubscriptionsController::class, 'store']);
});
