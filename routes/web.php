<?php

use Illuminate\Support\Facades\Route;
use Insane\Treasurer\Http\Controllers\BillingController;
use Insane\Treasurer\Http\Controllers\V2\PlansController as V2PlansController;
use Insane\Treasurer\Http\Controllers\V2\ProductsController as V2ProductsController;
use Insane\Treasurer\Http\Controllers\V2\SubscriptionsController as V2SubscriptionsController;
use Illuminate\Http\Request;

// resource route
Route::middleware(['web', 'treasurer.biller'])->group(function() {
    Route::get('/v2/subscriptions/return', [V2SubscriptionsController::class, 'return'])->name('paypal.return.2');
    Route::get('/v2/subscriptions/{planId}/subscribe', [V2SubscriptionsController::class, 'subscribe'])->name('paypal.subscribe.2');
    Route::post('/v2/subscriptions/{subscriptionId}/save', [V2SubscriptionsController::class, 'save'])->name('paypal.save.2');

    Route::post('/v2/subscriptions/{id}/agreement/{agreementId}/cancel', [V2SubscriptionsController::class, 'paypalCancel'])->name('paypal.cancel.2');
    Route::post('/v2/subscriptions/{id}/agreement/{agreementId}/reactivate', [V2SubscriptionsController::class, 'paypalReactivate'])->name('paypal.reactivate.2');
    Route::post('/v2/subscriptions/{id}/agreement/{agreementId}/suspend', [V2SubscriptionsController::class, 'paypalSuspend'])->name('paypal.suspend.2');

    Route::get('/billing', [BillingController::class, 'show'])->name('billing.show');
    Route::get('/billing/payments', [BillingController::class, 'index'])->name('billing.index');
    Route::get('/billing/upgrade', [BillingController::class, 'upgrade'])->name('billing.upgrade');

    Route::get('/user/invoice/{invoice}', function (Request $request, $invoiceId) {
        return $request->user()->downloadInvoice($invoiceId, [
            'vendor' => 'Your Company',
            'product' => 'Your Product',
        ]);
    });
});

Route::middleware(['treasurer.biller'])->prefix("api/v2")->group(function () {
    Route::get('/products', [V2ProductsController::class, 'index']);
    Route::post('/products', [V2ProductsController::class, 'store']);

    Route::get('/plans', [V2PlansController::class, 'index']);
    Route::get('/plans/{id}', [V2PlansController::class, 'index']);
    Route::post('/plans', [V2PlansController::class, 'store']);

    Route::get('/subscriptions/{id}', [V2SubscriptionsController::class, 'index']);
    Route::post('/subscriptions', [V2SubscriptionsController::class, 'store']);
});
