<?php

namespace Insane\Treasurer\Http\Controllers\V2;

use Exception;
use Illuminate\Http\Request;
use Insane\Treasurer\PaypalServiceV2;

class SubscriptionsController
{
    public function index($id = null) {
        $paypalService = new PaypalServiceV2();
        try {
            return $paypalService->getSubscriptions($id);
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

    public function save(Request $request) {
        $subscriptionId = $request->subscriptionId;
        $data = $request->post();
        return $request->user()->saveSubscription($subscriptionId, $data);
    }

    public function return(Request $request) {
        // try {
        //     // Execute agreement
        //     $paypalService = new PaypalServiceV2();
        //     $$paypalSubscription = $paypalService->executeSubscription($request->token);
        //     $user = $request->user();

        //     $subscription = Subscription::createFromPlan($paypalSubscription, $user);

        //     return redirect('/user/billing');
        // } catch (Exception $ex) {
        //     if ($user->agreement_id) {
        //         $user->reactivatePlan();
        //     }
        //     echo $ex->getMessage();
        // }
    }
}
