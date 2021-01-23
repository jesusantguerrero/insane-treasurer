<?php

namespace Insane\Treasurer\Http\Controllers\V2;

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
