<?php

namespace Insane\Treasurer\Concerns;

use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\PaypalService;

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
     * Get a subscription instance by name.
     *
     * @param  string  $name
     * @return \Laravel\Cashier\Subscription|null
     */
    public function subscription($name = 'default')
    {
        return $this->subscriptions->where('name', $name)->first();
    }

    /**
     * Get all of the subscriptions for the Stripe model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, $this->getForeignKey())->orderBy('created_at', 'desc');
    }
}
