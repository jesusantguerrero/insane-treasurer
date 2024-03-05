<?php

namespace Insane\Treasurer\Services;

use Exception;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\Models\SubscriptionPlan;
use Insane\Treasurer\Libraries\Paypal\PaypalClient;

class LocalBillingService {
    private $apiContext;

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

    public function syncPlans(mixed $localPlans) {
        foreach ($localPlans as $plan) {
            SubscriptionPlan::createFromConfig($plan);
            echo $plan['name'];
        }
    }

    // Subscriptions
    public function getSubscriptions($id) {
        return $this->apiContext->subscription->get($id);
    }

    public function subscribe($planId, $user, $biller) {
        Subscription::createFromLocal($planId, $user, $biller);
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
