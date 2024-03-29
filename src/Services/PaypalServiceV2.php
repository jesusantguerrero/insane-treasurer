<?php

namespace Insane\Treasurer\Services;


// Used to process plans

use Exception;
use GuzzleHttp\Client;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use Insane\Treasurer\Models\SubscriptionPlan;
use Insane\Treasurer\Libraries\Paypal\PaypalClient;

class PaypalServiceV2 {
    private $apiContext;
    private $accessToken;
    private $scope;
    private $tokenType;
    private $appId;
    private $expiresIn;
    private $nonce;
    private $client_id;
    private $secret;
    private const SANDBOX_URL = "https://api-m.sandbox.paypal.com/v1/";

    // Create a new instance with our paypal credentials
    public function __construct()
    {

        $this->setSettings();
        $this->setApiContext();
    }


    private function setSettings() {
        // Detect if we are running in live mode or sandbox
           if(config('treasurer.settings.mode') == 'live'){
            $this->client_id = config('treasurer.live_client_id');
            $this->secret = config('treasurer.live_secret');
        } else {
            $this->client_id = config('treasurer.sandbox_client_id');
            $this->secret = config('treasurer.sandbox_secret');
        }
    }

    static function getSettings() {
        // Detect if we are running in live mode or sandbox
        $mode = config('treasurer.settings.mode') == 'live' ? 'live' : 'sandbox';
        $settings = [
            "client_id" => config("treasurer.{$mode}_client_id"),
            "secret" => config("treasurer.{$mode}_secret")
        ];
        return $settings;
    }

    public function getProducts($id = null) {
        return $this->apiContext->product->get($id);
    }

    public function createProducts($data) {
        return $this->apiContext->product->store($data);
    }
    // Plans
    public function getPlans($id = null) {
        return $this->apiContext->plan->get($id);
    }

    public function createPlans($data) {
        return $this->apiContext->plan->store($data);
    }

    public function syncPlans() {
        $localPlans = config('treasurer.plans');
        foreach ($localPlans as $plan) {
            if (isset($plan['paypal_plan_id'])) {
                $planObject = $this->getPlans($plan['paypal_plan_id']);
                SubscriptionPlan::createFromPaypalV2($planObject, $plan);

                echo $plan['name'];
            }
        }
    }

    // Subscriptions
    public function getSubscriptions($id) {
        return $this->apiContext->subscription->get($id);
    }

    public function createSubscriptions($data) {
        return $this->apiContext->subscription->store($data);
    }

    public function subscribe($planId) {
        $data = [
            "plan_id" => $planId
        ];

        try {
          // Create agreement
          $agreement = $this->createSubscriptions($data);
          // Extract approval URL to redirect user
          return  $agreement->links[0]->href;
        } catch (Exception $ex) {
          throw new Exception($ex->getMessage());
        }
    }

    public function approveOrder($data) {
        return $this->apiContext->subscription->approveOrder($data);
    }

    public function suspendSubscription($id) {
        return $this->apiContext->subscription->suspend($id);
    }

    public function reactivateSubscription($id) {
        return $this->apiContext->subscription->reactivate($id);
    }

    public function cancelSubscription($id) {
        return $this->apiContext->subscription->cancel($id);
    }

    public function subscriptionTransactions($id) {
        return $this->apiContext->subscription->transactions($id);
    }

    public function subscriptionTransaction($id) {
        return $this->apiContext->subscription->transaction($id);
    }

    // api

    public function setApiContext() {
        $this->apiContext = new PaypalClient();
    }
}
