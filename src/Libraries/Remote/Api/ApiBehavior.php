<?php
namespace Insane\Treasurer\Libraries\Remote\Api;

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
            die();
            return $e->getMessage();
        }
        return $id ? json_decode($response->getBody()) : json_decode($response->getBody());
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
