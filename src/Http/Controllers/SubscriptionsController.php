<?php

namespace Insane\Treasurer\Http\Controllers;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Treasurer\PaypalService;
use Insane\Treasurer\Models\Subscription;

class SubscriptionsController
{

    public function __construct()
    {
        $this->model = new Subscription();
        $this->searchable = ['name'];
        $this->validationRules = [];
    }
    // Paypal agreement crud
    public function getAgreement($id, $agreementId) {
        $subscription = Subscription::find($id);
        if ($subscription && $subscription->customer_id == $agreementId) {
            return PaypalService::getAgreement($agreementId);
        }
    }

    // Paypal Subscription flow
    public function paypalSubscribe(Request $request) {
        // Create a new billing plan
        if ($agreementId = $request->user()->agreement_id) {
            $this->paypalSuspend(null, $agreementId);
        }

        $planId = $request->planId;

        // Create new agreement
        $paypalService = new PaypalService();
        try {
            $approvalUrl = $paypalService->createAgreement($planId);
            return redirect($approvalUrl);
        } catch (Exception $e) {
            return $e->getMessage();
        }

    }

    public function paypalReturn(Request $request, Response $response) {
        try {
            // Execute agreement
            $paypalService = new PaypalService();
            $agreement = $paypalService->executeAgreement($request->token);
            $definitions = $agreement->getAgreementDetails();

            $subscription = Subscription::create([
                "user_id" => $request->user()->id,
                "name" => $agreement->description,
                "agreement_id" => $agreement->id,
                "customer_id" => $agreement->getPayer()->getPayerInfo()->getPayerId(),
                "status" => $agreement->state,
                "plan_id" => $agreement->getPlan()->getId(),
                "paypal_plan_id" => $agreement->plan->id,
                "quantity" => $agreement->getPlan()->getPaymentDefinitions()[0]->getAmount()->value,
                "last_payment_date" => (new Carbon($definitions->getLastPaymentDate()))->toDateTimeString(),
                "next_billing_date" => (new Carbon($definitions->getNextBillingDate()))->toDateTimeString(),
                "last_payment" => $definitions->getLastPaymentAmount(),
                "next_payment" => $definitions->getOutstandingBalance(),
            ]);

            $user = $request->user();

            if ($user->agreement_id) {
                $this->paypalCancel($response, null, $request->user()->agreement_id);
            }

            if(isset($agreement->id)){
                $user->customer_id = $subscription->customer_id;
                $user->plan_id = $subscription->plan_id;
                $user->agreement_id = $subscription->agreement_id;
            }
            $user->save();
            return redirect('/user/billing');
        } catch (Exception $ex) {
            if ($user->agreement_id) {
                $this->paypalReactivate(null ,$request->user()->agreement_id);
            }
            echo $ex->getMessage();
        }
    }

    // Agreements operations
    public function paypalCancel(Response $response, $id, $agreementId) {
        $subscription = $this->getSubscriptionByAgreementId($agreementId);

        if ($subscription && $subscription->agreement_id == $agreementId) {
            try {
                $agreement = PaypalService::cancelAgreement($agreementId);
                $subscription->update([
                    "status" => $agreement->state
                ]);

                return [$agreement, $subscription];
            } catch (Exception $e) {
                return $response->setContent($e->getMessage())->setStatus(404);
            }
        }
    }

    public function paypalReactivate($id, $agreementId) {
        $subscription = $this->getSubscriptionByAgreementId($agreementId);
        if ($subscription && $subscription->agreement_id == $agreementId) {
            $agreement = PaypalService::reactivateAgreement($agreementId);
            $subscription->update([
                "status" => $agreement->state
            ]);
            return $agreement;
        }
    }

    public function paypalSuspend($id, $agreementId) {
        $subscription = $this->getSubscriptionByAgreementId($agreementId);
        if ($subscription && $subscription->agreement_id == $agreementId) {
            $agreement = PaypalService::suspendAgreement($agreementId);
            $subscription->update([
                "status" => $agreement->state
            ]);
            return $agreement;
        }
    }

    private function getSubscriptionByAgreementId($agreementId) {
        $subscriptions = Subscription::where([
            "agreement_id" => $agreementId
        ])->get() ;

        if (count($subscriptions)) {
            return $subscriptions[0];
        }
    }
}
