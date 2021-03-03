<?php
namespace Insane\Treasurer\Libraries\Paypal\Api;

use Exception;
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

    public function transactions($id, $params = "start_time=2018-01-21T07:50:20.940Z&end_time=2021-08-21T07:50:20.940Z&fields=all") {
        $url = $this->endpoint . "/$id/transactions?$params";
        try {
            $response = $this->apiContext->client->get($url);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return json_decode($response->getBody());
    }

    public function approveOrder($data) {
        $result = $this->apiContext->client->request('POST', "https://api-m.sandbox.paypal.com/v2/checkout/orders/{$data['order_id']}", [
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer {$data['access_token']}"
            ],
            "body" => json_encode($data)
        ]);

        return json_decode($response->getBody());
    }

}
