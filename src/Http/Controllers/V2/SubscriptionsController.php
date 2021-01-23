<?php

namespace Insane\Treasurer\Http\Controllers\V2;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Treasurer\PaypalServiceV2;

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

    public function save(Request $request) {
        $subscriptionId = $request->subscriptionId;
        $data = $request->post();
        return $request->user()->saveSubscription($subscriptionId, $data);
    }

    // Agreements operations
    public function paypalCancel(Request $request, $id, $agreementId) {
        return $request->user()->cancelSubscription($agreementId, $request->post());
    }

    public function paypalReactivate(Request $request, $id, $agreementId) {
        return $request->user()->reactivateSubscription($agreementId, $request->post());
    }

    public function paypalSuspend(Request $request, $id, $agreementId) {
        return $request->user()->suspendSubscription($agreementId, $request->post());
    }
}
