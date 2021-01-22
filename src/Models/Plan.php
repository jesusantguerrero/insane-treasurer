<?php

namespace Insane\Treasurer\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [ "user_id", "name", "paypal_plan_id", "quantity", "details", "paypal_plan_status"];

    public static function createFromPaypalV2($paypalPlan, $userId) {
        $priceIndex = array_search("regular", array_column($paypalPlan->billing_cycles, 'tenure_type'));

        return self::create([
            "user_id" => $userId,
            "name" => $paypalPlan->name,
            "paypal_plan_id" => $paypalPlan->id,
            "paypal_plan_status" => $paypalPlan->status,
            "quantity" => $priceIndex ? $paypalPlan->billing_cycles[$priceIndex]->pricing_scheme->fixed_price->value : 0,
            "details" => json_encode($paypalPlan->links)
        ]);
    }
}
