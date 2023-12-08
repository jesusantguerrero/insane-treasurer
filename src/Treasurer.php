<?php

namespace Insane\Treasurer;

use Insane\Treasurer\Models\Plan;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\Models\SubscriptionItem;
use Insane\Treasurer\Contracts\BillableEntity;

class Treasurer
{
    const VERSION = '0.2.0';

    const PAYPAL_VERSION = '2023-12-08';

    protected static $formatCurrencyUsing;
    public static $runsMigrations = true;
    public static $registersRoutes = true;
    public static $deactivatePastDue = true;
    public static $deactivateIncomplete = true;
    public static $calculatesTaxes = false;
    public static $customerModel = 'App\\Models\\User';
    public static $subscriptionModel = Subscription::class;
    public static $subscriptionItemModel = SubscriptionItem::class;
    public static $planModel = Plan::class;

    public static function useCustomerModel(string $model) {
        static::$customerModel = $model;
    }

    public static function ignoreMigrations() {
        static::$runsMigrations = false;
    }

    public static function findBillableBySubscription($subscriptionId) {
        return $subscriptionId ? Subscription::where('agreement_id', $subscriptionId)->first()->biller: null;
    }

    public static function findBillable($paypalId) {
        return $paypalId ? (new static::$customerModel)
            ->where('customer_id', $paypalId)->get() : null;
    }

    public static function useBiller($callback) {
        return app()->singleton(BillableEntity::class, $callback);
    }
}
