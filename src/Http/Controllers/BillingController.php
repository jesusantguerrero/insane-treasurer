<?php

namespace Insane\Treasurer\Http\Controllers;

use Illuminate\Http\Request;
use Insane\Treasurer\Invoice;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\Models\SubscriptionPlan;
use Insane\Treasurer\Contracts\BillableEntity;

class BillingController
{
    private $billable;

    public function __construct(BillableEntity $billable)
    {
        $this->model = new SubscriptionPlan();
        $this->searchable = ['name'];
        $this->validationRules = [];
        $this->billable = $billable;
    }

    public function show(Request $request) {
        $biller = $this->billable->resolve($request);

        return inertia('Billing/Show', [
            "plans" => SubscriptionPlan::orderBy('quantity')->get(),
            "subscriptions" => $biller ? $biller->subscriptions : [],
            "transactions" => []
        ]);
    }

    public function upgrade(Request $request) {
        $user = $request->user();
        $biller = $this->billable->resolve($request);

        return inertia('Billing/Upgrade', [
            "plans" => SubscriptionPlan::orderBy('quantity')->get(),
            "subscriptions" => Subscription::where([
                "user_id" => $user->id
            ])->get(),
            "transactions" => fn () => $biller->subscriptionTransactions()
        ]);
    }

    public function index(Request $request) {
        $biller = $this->billable->resolve($request);

        return inertia('Billing/Payments', [
            "transactions" => fn () => $biller->subscriptionTransactions()
        ]);
    }

    public function getTransactionPDF(Request $request, $id) {
        $transaction = $request->user()->getSubscriotionTransaction($id);
        $invoice = new Invoice($request->user(), $transaction);
        return $invoice->transactionPDF();

    }
}
