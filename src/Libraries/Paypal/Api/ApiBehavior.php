<?php
namespace Insane\Treasurer\Libraries\Paypal\Api;

use Exception;

trait ApiBehavior {
    protected $endpoint;
    protected $apiContext;
    protected $resultName;

    public function get($id = null) {
        $url = $id ? $this->endpoint . "/$id" : $this->endpoint;
        try {
            $response = $this->apiContext->client->get($url);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        $name = $this->resultName;
        return $id ? json_decode($response->getBody()) : json_decode($response->getBody())->$name;
    }

    public function store($data) {
        $result = $this->apiContext->client->request('POST', $this->endpoint, [
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "body" => json_encode($data)
        ]);

        return $result->getBody();
    }

    public function delete() {

    }

    public function update() {

    }
}
