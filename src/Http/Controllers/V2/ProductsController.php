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

class ProductsController
{

    public function __construct()
    {
        $this->model = new SubscriptionPlan();
        $this->searchable = ['name'];
        $this->validationRules = [];
    }


    public function index() {
        $paypalService = new PaypalServiceV2();
        try {
            $products = $paypalService->getProducts();
            return $products;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    public function store(Request $request) {
        $paypalService = new PaypalServiceV2();
        $data = $request->post();
        return $paypalService->createProducts($data);
    }
}
