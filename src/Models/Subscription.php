<?php
namespace Insane\Treasurer\Models;

use Illuminate\Database\Eloquent\Model;
use Insane\Treasurer\PaypalService;

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
}
