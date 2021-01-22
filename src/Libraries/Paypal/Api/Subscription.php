<?php
namespace Insane\Treasurer\Libraries\Paypal\Api;

use Insane\Treasurer\Libraries\Paypal\Auth\ApiContext;

class Subscription {
    private const ENDPOINT = "billing/subscriptions";
    use ApiBehavior;

    public function __construct( ApiContext $apiContext)
    {
        $this->apiContext = $apiContext;
        $this->endpoint = self::ENDPOINT;
    }


    public function suspend($id) {
        $this->apiContext->client->request('POST', $this->endpoint. "/$id/suspend", [
            "headers" => [
                "Content-Type" => "application/json"
            ]
        ]);
        return $this->get($id);
    }

    public function reactivate($id) {
        $this->apiContext->client->request('POST', $this->endpoint. "/$id/activate", [
            "headers" => [
                "Content-Type" => "application/json"
            ]
        ]);
        return $this->get($id);
    }

    public function cancel($id) {
        $this->apiContext->client->request('POST', $this->endpoint. "/$id/cancel", [
            "headers" => [
                "Content-Type" => "application/json"
            ]
        ]);
        return $this->get($id);
    }

}
