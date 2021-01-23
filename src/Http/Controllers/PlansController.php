<?php

namespace Insane\Treasurer\Http\Controllers;

use Insane\Treasurer\PaypalService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Treasurer\Models\Plan as ModelsPlan;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\PaypalServiceV2;
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

    public function index() {
        $paypalService = new PaypalServiceV2();
        try {
            return $paypalService->getProducts();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function storeProducts(Request $request) {
        $paypalService = new PaypalServiceV2();
        $data = $request->post();
        return $paypalService->createProducts($data);
    }
}
