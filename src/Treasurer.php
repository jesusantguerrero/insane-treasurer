<?php

namespace Insane\Treasurer;

use Illuminate\Http\Request;
use Insane\Treasurer\Contracts\BillableEntity;
use Insane\Treasurer\Models\Subscription;

class Treasurer
{
    public static $customerModel = 'App\\Models\\User';

    public static function useCustomerModel(string $model) {
        static::$customerModel = $model;
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
