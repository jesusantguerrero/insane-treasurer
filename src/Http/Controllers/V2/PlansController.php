<?php

namespace Insane\Treasurer\Http\Controllers\V2;

use Exception;
use PayPal\Api\Agreement;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Insane\Treasurer\PaypalService;
use Insane\Treasurer\PaypalServiceV2;
use Insane\Treasurer\Models\Subscription;
use Insane\Treasurer\Models\SubscriptionPlan;

class PlansController
{

    public function __construct()
    {
        $this->model = new SubscriptionPlan();
        $this->searchable = ['name'];
        $this->validationRules = [];
    }


    public function index($id = null) {
        $paypalService = new PaypalServiceV2();
        try {
            return $paypalService->getPlans($id);
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function store(Request $request) {
        $paypalService = new PaypalServiceV2();
        $data = $request->post();
        return $paypalService->createPlans($data);
    }
}
