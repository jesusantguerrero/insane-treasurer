<?php
namespace Insane\Treasurer\Libraries\Paypal\Api;

use Exception;
use GuzzleHttp\Client;
use Insane\Treasurer\Libraries\Paypal\Auth\ApiContext;

class Product {
    private ApiContext $apiContext;
    private const ENDPOINT = "catalogs/products";
    public function __construct( ApiContext $apiContext)
    {
        $this->apiContext = $apiContext;
    }


    public function get($id = null) {
        $url = $id ? self::ENDPOINT . "/$id" : self::ENDPOINT;
        try {
            $response = $this->apiContext->client->get($url);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return $id ? json_decode($response->getBody()) : $response->getBody();
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
