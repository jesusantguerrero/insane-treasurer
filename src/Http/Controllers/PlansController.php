<?php

namespace Insane\Paypal\Http\Controllers;

use Insane\Paypal\PaypalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Paypal\Models\Plan as ModelsPlan;
use Insane\Paypal\Models\Subscription;
use PayPal\Api\Agreement;

class PlansController
{

    public function __construct()
    {
        $this->model = new ModelsPlan();
        $this->searchable = ['name'];
        $this->validationRules = [];
    }

    public function store(Request $request, Response $response){
        // Create a new billing plan
        $data = $request->post();
        $paypalService = new PaypalService();
        $paypalPlan = $paypalService->createPlan($data, $data['payments']);
        $plan = $this->model->create([
            "user_id" => 1,
            "name" => $paypalPlan->name,
            "paypal_plan_id" => $paypalPlan->id,
            "paypal_plan_status" => $paypalPlan->state,
            "quantity" => $paypalPlan->getPaymentDefinitions()[0]->getAmount()->value,
            "details" => json_encode($paypalPlan->getPaymentDefinitions())
        ]);
        return $response->setContent($plan);
    }
}
