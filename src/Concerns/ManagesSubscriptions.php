<?php

namespace Insane\Treasurer\Concerns;

use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\PaypalService;
use Insane\Treasurer\PaypalServiceV2;

trait ManagesSubscriptions
{
    /**
     * Begin creating a new subscription.
     *
     * @param  string  $name
     * @param  string|string[]  $plans
     * @return \Insane\Treasurer\SubscriptionBuilder
     */
    public function newSubscription($name, $plans)
    {
        $paypalService = new PaypalService();
        foreach ($plans as $plan) {
            return $paypalService->createPlan($plan, $plan['payments']);
        }
    }

    /**
     * Determine if the Stripe model is on trial.
     *
     * @param  string  $name
     * @param  string|null  $plan
     * @return bool
     */
    public function onTrial($name = 'default', $plan = null)
    {
        if (func_num_args() === 0 && $this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->onTrial()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * Determine if the Stripe model is on a "generic" trial at the model level.
     *
     * @return bool
     */
    public function onGenericTrial()
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Get the ending date of the trial.
     *
     * @param  string  $name
     * @return \Illuminate\Support\Carbon|null
     */
    public function trialEndsAt($name = 'default')
    {
        if ($subscription = $this->subscription($name)) {
            return $subscription->trial_ends_at;
        }

        return $this->trial_ends_at;
    }

    /**
     * Determine if the Stripe model has a given subscription.
     *
     * @param  string  $name
     * @param  string|null  $plan
     * @return bool
     */
    public function subscribed($name = 'default', $plan = null)
    {
        $subscription = $this->subscription($name);

        if (! $subscription || ! $subscription->valid()) {
            return false;
        }

        return $plan ? $subscription->hasPlan($plan) : true;
    }

    /**
     * Suspend a subscription in paypal.
     *
     * @param  string  $name
     * @return \Insane\Treasurer\Models\Subscription|null
     */
    public function suspendSubscription($subscriptionId)
    {
        $paypalService = new PaypalServiceV2();
        $subscription = $paypalService->suspendSubscription($subscriptionId);
        $localSubscription = Subscription::updateStatus($subscription->status, $subscription->id);
        return $localSubscription;
    }

    /**
     * Reactivate a subscription in paypal.
     *
     * @param  string  $name
     * @return \Insane\Treasurer\Models\Subscription|null
     */
    public function reactivateSubscription($subscriptionId)
    {
        $paypalService = new PaypalServiceV2();
        $subscription = $paypalService->reactivateSubscription($subscriptionId);
        $localSubscription = Subscription::updateStatus($subscription->status, $subscription->id);
        return $localSubscription;
    }

    /**
     * Cancel a subscription in paypal.
     *
     * @param  string  $name
     * @return \Insane\Treasurer\Models\Subscription|null
     */
    public function cancelSubscription($subscriptionId)
    {
        $paypalService = new PaypalServiceV2();
        $subscription = $paypalService->cancelSubscription($subscriptionId);
        $localSubscription = Subscription::updateStatus($subscription->status, $subscription->id);

        if($localSubscription){
            $this->customer_id = "";
            $this->plan_id = "";
            $this->agreement_id = "";
            $this->save();
        }
        return $localSubscription;
    }


    /**
     * get transactions.
     *
     * @param  string  $name
     * @return \Insane\Treasurer\Models\Subscription|null
     */
    public function subscriptionTransactions($subscriptionId = null)
    {
        $paypalService = new PaypalServiceV2();
        return $paypalService->subscriptionTransactions($subscriptionId ?? $this->agreement_id);
    }

    /**
     * Get the active subscription instance.
     *
     * @param  string  $name
     * @return \Treasurer\Models\Subscription
     */
    public function subscription()
    {
        return $this->subscriptions->where('status', 'ACTIVE')->first();
    }

    /**
     * Suspend a subscription in paypal.
     *
     * @param  string  $name
     * @return \Insane\Treasurer\Models\Subscription|null
     */
    public function getPaypalSubscription()
    {
        $paypalService = new PaypalServiceV2();
        $subscription = $this->subscription();
        return $paypalService->getSubscriptions($subscription->agreement_id);
    }

    /**
     * Get all of the subscriptions for the Stripe model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->morphMany(Subscription::class, 'subscribable')->orderBy('created_at', 'desc');
    }
}
