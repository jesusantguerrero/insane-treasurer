<?php
namespace Insane\Treasurer\Libraries\Paypal\Api;

use Exception;

trait ApiBehavior {
    protected $endpoint;
    protected $apiContext;

    public function get($id = null) {
        $url = $id ? $this->endpoint . "/$id" : $this->endpoint;
        try {
            $response = $this->apiContext->client->get($url);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $id ? $response->getBody() : $response->getBody();
    }

    public function store($data) {
        $result = $this->apiContext->client->request('POST', 'catalogs/products', [
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
