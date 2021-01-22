<?php

namespace Insane\Treasurer;


// Used to process plans

use Exception;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use GuzzleHttp\Client;

class PaypalServiceV2 {
    private $apiContext;
    private $accessToken;
    private $scope;
    private $tokenType;
    private $appId;
    private $expiresIn;
    private $nonce;
    private $client_id;
    private $secret;
    private const SANDBOX_URL = "https://api-m.sandbox.paypal.com/v1/";

    // Create a new instance with our paypal credentials
    public function __construct()
    {

        $this->setSettings();
        $this->setApiContext();
    }


    private function setSettings() {
        // Detect if we are running in live mode or sandbox
           if(config('paypal.settings.mode') == 'live'){
            $this->client_id = config('paypal.live_client_id');
            $this->secret = config('paypal.live_secret');
        } else {
            $this->plan_id = getenv('PAYPAL_SANDBOX_PLAN_ID');
            $this->client_id = config('paypal.sandbox_client_id');
            $this->secret = config('paypal.sandbox_secret');
        }
    }

    static function getSettings() {
        // Detect if we are running in live mode or sandbox
           $mode = config('paypal.settings.mode') == 'live' ? 'live' : 'sandbox';
            $settings = [
                "client_id" => config("paypal.{$mode}_client_id"),
                "secret" => config("paypal.{$mode}_secret")
            ];
            return $settings;
    }

    public function getProducts() {
        try {
            $response = $this->apiContext->get('catalogs/products');
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return $response;
    }

    public function createProducts($data) {
        return $this->apiContext->request('POST', 'catalogs/products', [
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "body" => json_encode($data)
        ]);
    }
    // Plans
    public function getPlans($id = null) {
        $url = $id ? "billing/plans/$id" : 'billing/plans';
        $result = $this->apiContext->request('GET', $url);
        return json_decode($result->getBody());
    }

    public function createPlans($data) {
        return $this->apiContext->request('POST', 'billing/plans', [
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "body" => json_encode($data)
        ]);
    }

    // Subscriptions
    public function getSubscriptions($id) {
        $result = $this->apiContext->request('GET', "billing/subscriptions/$id");
        return json_decode($result->getBody());
    }

    public function createSubscriptions($data) {
        $result = $this->apiContext->request('POST', 'billing/subscriptions', [
            "headers" => [
                "Content-Type" => "application/json"
            ],
            "json" => $data
        ]);

        return json_decode($result->getBody());
    }

    public function subscribe($planId) {
        $data = [
            "start_time" => \Carbon\Carbon::now()->addMinutes(5)->toIso8601String(),
            "plan_id" => $planId,
            "subscriber" => [
                "name" => [
                  "given_name" => "SET_PROVIDED_NAME",
                  "surname"=> "SET_PROVIDED_SURENAME"
                ],
                "email_address" => "SET_PROVIDED_EMAIL"
            ],
            "application_context" => [
                "return_url" => config('app.url'). "/v2/subscriptions/return",
                "cancel_url" => config('app.url'). "/v2/subscriptions/return"
            ]
        ];

        try {
          // Create agreement
          $agreement = $this->createSubscriptions($data);
          // Extract approval URL to redirect user
          return  $agreement->links[0]->href;
        } catch (Exception $ex) {
          throw new Exception($ex->getMessage());
        }
    }

    public function setApiContext() {
        $client = new Client();
        $settings = self::getSettings();
        $result = $client->post(self::SANDBOX_URL . "/oauth2/token", [
            "auth" => [$settings['client_id'], $settings['secret']],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ]
        ]);
        $body = json_decode($result->getBody());

        $this->scope = $body->scope;
        $this->accessToken = $body->access_token;
        $this->tokenType =  "Bearer";
        $this->appId = $body->app_id;
        $this->expiresIn = $body->expires_in;
        $this->nonce = $body->nonce;

        $this->apiContext = new Client([
            "base_uri" => self::SANDBOX_URL,
            "headers" => [
                "Authorization" => "$this->tokenType ". $this->accessToken
            ]
        ]);

        // dump($this->apiContext);
        // die();
    }
}
