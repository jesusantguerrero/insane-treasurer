<?php
namespace Insane\Treasurer;

// use Insane\Treasurer\Concerns\ManagesCustomer;
use Insane\Treasurer\Concerns\ManagesInvoices;
// use Insane\Treasurer\Concerns\ManagesPaymentMethods;
use Insane\Treasurer\Concerns\ManagesSubscriptions;
use Insane\Treasurer\Models\Plan;
use Insane\Treasurer\Models\Subscription;

// use Insane\Treasurer\Concerns\PerformsCharges;

trait Billable
{
    // use ManagesCustomer;
    use ManagesInvoices;
    // use ManagesPaymentMethods;
    use ManagesSubscriptions;
    // use PerformsCharges;

    public function resolve($callback) {
        return $callback;
    }

    public function subscription() {
        return $this->morphOne(Subscription::class, 'subscribable');
    }

    public function plan() {
        return $this->hasOneThrough(Plan::class, Subscription::class);
    }
}
