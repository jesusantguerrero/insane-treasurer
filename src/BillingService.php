<?php

namespace Insane\Treasurer;


// Used to process plans

use Exception;
use Insane\Treasurer\Libraries\Paypal\PaypalClient;
use Insane\Treasurer\Services\LocalBillingService;
use Insane\Treasurer\Services\PaypalServiceV2;

class BillingService {
    private $apiContext;

    private $serviceProvider;

    // Create a new instance with our paypal credentials
    public function __construct()
    {
        $driver = config('treasurer.driver');

        if ($driver == 'paypal') {
            $this->serviceProvider = new PaypalServiceV2();
        } else {
            $this->serviceProvider = new LocalBillingService();
        }
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
        $this->serviceProvider->syncPlans($localPlans);
    }

    // Subscriptions
    public function getSubscriptions($id) {
        return $this->serviceProvider->getSubscriptions($id);
    }

    public function createSubscriptions($data) {
        return $this->serviceProvider->createSubscriptions($data);
    }

    public function subscribe($planId, $user, $biller) {
        return $this->serviceProvider->subscribe($planId, $user, $biller);
    }

    public function approveOrder($data) {
        return $this->serviceProvider->approveOrder($data);
    }

    public function suspendSubscription($id) {
        return $this->serviceProvider->suspendSubscription($id);
    }

    public function reactivateSubscription($id) {
        return $this->serviceProvider->reactivateSubscription($id);
    }

    public function cancelSubscription($id) {
        return $this->serviceProvider->cancelSubscription($id);
    }

    public function subscriptionTransactions($id) {
        return $this->serviceProvider->subscriptionTransactions($id);
    }

    public function subscriptionTransaction($id) {
        return $this->serviceProvider->subscriptionTransaction($id);
    }
}
