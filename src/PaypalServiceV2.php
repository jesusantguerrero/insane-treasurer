<?php

namespace Insane\Treasurer;


// Used to process plans

use Exception;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use GuzzleHttp\Client;
use Insane\Treasurer\Libraries\Paypal\PaypalClient;
use Insane\Treasurer\Models\Plan;

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
           if(config('paypal.settings.mode') == 'live'){
            $this->client_id = config('paypal.live_client_id');
            $this->secret = config('paypal.live_secret');
        } else {
            $this->plan_id = getenv('PAYPAL_SANDBOX_PLAN_ID');
            $this->client_id = config('paypal.sandbox_client_id');
            $this->secret = config('paypal.sandbox_secret');
        }
    }

    static function getSettings() {
        // Detect if we are running in live mode or sandbox
           $mode = config('paypal.settings.mode') == 'live' ? 'live' : 'sandbox';
            $settings = [
                "client_id" => config("paypal.{$mode}_client_id"),
                "secret" => config("paypal.{$mode}_secret")
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

    public function syncPlans($userId) {
        $plans = $this->getPlans();
        foreach ($plans as $plan) {
            $planObject = $this->getPlans($plan->id);
            Plan::createFromPaypalV2($planObject, $userId);
        }
    }

    public function syncPayments($userId) {
    //
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

    // api

    public function setApiContext() {
        $this->apiContext = new PaypalClient();
    }
}
