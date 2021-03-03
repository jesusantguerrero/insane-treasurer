<?php

namespace Insane\Treasurer\Http\Controllers;

use Insane\Treasurer\PaypalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Insane\Treasurer\Models\Plan;
use Insane\Treasurer\Invoice;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\PaypalServiceV2;
use Laravel\Jetstream\Jetstream;
use PayPal\Api\Agreement;

class BillingController
{

    public function __construct()
    {
        $this->model = new Plan();
        $this->searchable = ['name'];
        $this->validationRules = [];
    }

    public function show(Request $request) {
        $user = $request->user();

        return Jetstream::inertia()->render($request, 'Billing/Show', [
            "plans" => Plan::orderBy('quantity')->get(),
            "subscriptions" => Subscription::where([
                "user_id" => $user->id
            ])->get(),
            "transactions" => function () use ($request) {
                return $request->user()->subscriptionTransactions();
            }
        ]);
    }

    public function upgrade(Request $request) {
        $user = $request->user();

        return Jetstream::inertia()->render($request, 'Billing/Upgrade', [
            "plans" => Plan::orderBy('quantity')->get(),
            "subscriptions" => Subscription::where([
                "user_id" => $user->id
            ])->get(),
            "transactions" => function () use ($request) {
                return $request->user()->subscriptionTransactions();
            }
        ]);
    }

    public function index(Request $request) {
        return Jetstream::inertia()->render($request, 'Billing/Payments', [
            "transactions" => function () use ($request) {
                return $request->user()->subscriptionTransactions();
            }
        ]);
    }

    public function getTransactionPDF(Request $request, $id) {
        $transaction = $request->user()->getSubscriotionTransaction($id);
        $invoice = new Invoice($request->user(), $transaction);
        return $invoice->transactionPDF();

    }
}
