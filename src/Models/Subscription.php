<?php
namespace Insane\Treasurer\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Insane\Treasurer\PaypalService;
use Insane\Treasurer\PaypalServiceV2;

Class Subscription extends Model {
    protected $fillable = [
        "user_id",
        "name",
        "agreement_id",
        "customer_id",
        "status",
        "plan_id",
        "paypal_plan_id",
        "quantity",
        "last_payment_date",
        "next_billing_date",
        "last_payment",
        "next_payment"
    ];

    public function agreements() {
        return PaypalService::getAgreement($this->agreement_id)->toArray();
    }

    public static function createFromPaypalV2($agreement, $planId, $user) {
        $paypalService = new PaypalServiceV2();
        $plan = $paypalService->getPlans($planId);
        return self::create([
            "user_id" => $user->id,
            "name" => $plan->name,
            "agreement_id" => $agreement->id,
            "customer_id" => $agreement->subscriber->payer_id,
            "status" => $agreement->status,
            "plan_id" => $planId,
            "paypal_plan_id" => $planId,
            "quantity" => $agreement->billing_info->last_payment->amount->value,
            "last_payment_date" => (new Carbon($agreement->billing_info->last_payment->time))->toDateTimeString(),
            "next_billing_date" => (new Carbon($agreement->billing_info->next_billing_time))->toDateTimeString(),
            "last_payment" => json_encode($agreement->billing_info->last_payment),
            "next_payment" => json_encode($agreement->billing_info->outstanding_balance),
        ]);
    }
}
