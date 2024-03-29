<?php
namespace Insane\Treasurer\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Insane\Treasurer\Services\PaypalServiceV2;

Class Subscription extends Model {
    protected $fillable = [
        "user_id",
        "subscribable_id",
        "subscribable_type",
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

    protected $with = ['plan', 'biller'];

    public function biller() {
        return $this->morphTo(__FUNCTION__, 'subscribable_type', 'subscribable_id');
    }

    public function plan() {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function agreements() {
        $paypalService = new PaypalServiceV2();
        return PaypalService::getAgreement($this->agreement_id)->toArray();
    }

    public static function createFromPaypal($agreement, $planId, $user, $biller) {
        $paypalService = new PaypalServiceV2();
        $plan = $paypalService->getPlans($planId);
        return self::create([
            "user_id" => $user->id,
            "subscribable_id" => $biller->id,
            "subscribable_type" => get_class($biller),
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

    public static function createFromLocal($planId, $user, $biller) {
        $plan = SubscriptionPlan::find($planId);

        return self::create([
            "user_id" => $user->id,
            "subscribable_id" => $biller->id,
            "subscribable_type" => get_class($biller),
            "name" => $plan->name,
            "agreement_id" => '',
            "customer_id" => $user->id,
            "status" => 'active',
            "plan_id" => $planId,
            "paypal_plan_id" => $planId,
            "quantity" => $plan->quantity,
            "last_payment_date" => now()->toDateTimeString(),
            "next_billing_date" => now()->addMonthsNoOverflow(1),
            "last_payment" => $plan->quantity,
            "next_payment" => $plan->quantity,
        ]);
    }

    public static function updateStatus($status, $subscriptionId) {
        $subscription = self::getSubscriptionByAgreementId($subscriptionId);

        if ($subscription) {
            $subscription->update([
                    "status" => $status
                ]);
        }
    }

    public static function getSubscriptionByAgreementId($agreementId) {
        $subscriptions = Subscription::where([
            "agreement_id" => $agreementId
        ])->limit(1)->get();

        if (count($subscriptions)) {
            return $subscriptions[0];
        }
    }
}
