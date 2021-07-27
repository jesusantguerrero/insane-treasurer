<?php

namespace Insane\Treasurer\Http\Controllers\V2;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Treasurer\Contracts\BillableEntity;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\PaypalServiceV2;
use Insane\Treasurer\Treasurer;

class SubscriptionsController
{
    public function index(Response $response, $id = null) {
        $paypalService = new PaypalServiceV2();
        try {
            $result = $paypalService->getSubscriptions($id);
            return $response->setContent([
                "data" => $result
            ])->setStatusCode(RESPONSE::HTTP_OK);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function store(Request $request) {
        $paypalService = new PaypalServiceV2();
        $data = $request->post();
        return $paypalService->createSubscriptions($data);
    }

    public function subscribe(Request $request) {
        $paypalService = new PaypalServiceV2();
        $planId = $request->planId;
        $approvalLink = $paypalService->subscribe($planId);
        return redirect($approvalLink);
    }

    public function save(Request $request, BillableEntity $billable) {
        $subscriptionId = $request->subscriptionId;
        $data = $request->post();

        $paypalService = new PaypalServiceV2();
        $biller = $billable->resolve($request);
        $subscription = $paypalService->getSubscriptions($subscriptionId);
        $localSubscription = Subscription::createFromPaypal($subscription, $data['plan_id'], $request->user(), $biller);

        if (isset($localSubscription->agreement_id)) {
            $biller->customer_id = $localSubscription->customer_id;
            $biller->plan_id = $localSubscription->plan_id;
            $biller->agreement_id = $localSubscription->agreement_id;
            $biller->trial_ends_at = null;
            $biller->save();
            $localSubscription->subscribable_type = Treasurer::$customerModel;
            $localSubscription->subscribable_id = $biller->id;
            $localSubscription->save();
        }
        return $localSubscription;
    }

    // Agreements operations
    public function paypalCancel(Request $request, $id, $subscriptionId) {
        if ($biller = Treasurer::findBillableBySubscription($subscriptionId)) {
            return $biller->cancelSubscription($subscriptionId, $request->post());
        }
    }

    public function paypalReactivate(Request $request, $id, $subscriptionId) {
        if ($biller = Treasurer::findBillableBySubscription($subscriptionId)) {
            return $biller->reactivateSubscription($subscriptionId, $request->post());
        }
    }

    public function paypalSuspend(Request $request, $id, $subscriptionId) {
        if ($biller = Treasurer::findBillableBySubscription($subscriptionId)) {
            return $biller->suspendSubscription($subscriptionId, $request->post());
        }
    }
}
