<?php

namespace Insane\Treasurer\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [ "user_id", "name", "paypal_plan_id", "quantity", "details", "paypal_plan_status"];

    public static function createFromPaypalV2($paypalPlan, $planConfig) {
        $paypalPlan = is_array($paypalPlan) ? null : $paypalPlan;
        $priceIndex = !$paypalPlan ? false : array_search("regular", array_column($paypalPlan->billing_cycles, 'tenure_type'));

        return self::create([
            "user_id" => 0,
            "name" => $paypalPlan ? $paypalPlan->name : $planConfig['name'],
            "paypal_plan_id" => $paypalPlan ? $paypalPlan->id : $planConfig['paypal_plan_id'],
            "paypal_plan_status" => $paypalPlan ? $paypalPlan->status : 1,
            "features" => $planConfig['features'],
            "quantity" => $priceIndex !== false ? $paypalPlan->billing_cycles[$priceIndex]->pricing_scheme->fixed_price->value : $planConfig['price'],
            "details" => $paypalPlan ? json_encode($paypalPlan->links) : "[]"
        ]);
    }
}
