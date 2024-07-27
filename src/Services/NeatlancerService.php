<?php

namespace Insane\Treasurer\Services;


// Used to process plans

use Exception;
use Insane\Treasurer\Models\SubscriptionPlan;
use Insane\Treasurer\Libraries\Remote\RemoteClient;

class NeatlancerService {
    private $apiContext;
    private $accessToken;
    private $scope;
    private $tokenType;
    private $appId;
    private $expiresIn;
    private $nonce;
    private $client_id;
    private $secret;
    private $url;

    // Create a new instance with our paypal credentials
    public function __construct()
    {
        $this->setSettings();
        $this->setApiContext();
    }

    public function setApiContext() {
          $this->apiContext = new RemoteClient();
    }

    private function setSettings() {
        $this->url = config('treasurer.drivers.neatlancer.url');
        $this->client_id = config('treasurer.drivers.neatlancer.client_id');
        $this->secret = config('treasurer.drivers.neatlancer.secret_id');
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
        $plans = $this->getPlans();
        foreach ($plans->data as $plan) {
            SubscriptionPlan::createFromRemote((array) $plan);
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
}
