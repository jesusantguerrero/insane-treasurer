<?php
namespace Insane\Treasurer\Libraries\Remote\Auth;

use GuzzleHttp\Client;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class ApiContext {

    public Client $client;
    private $accessToken;
    private $tokenType;
    private $client_id;
    private $url;
    private $secret;
    private const GRAND_TYPE = "client_credentials";

    public function __construct($options)
    {
        print_r($options);
        $this->client_id = $options['client_id'];
        $this->secret = $options['secret'];
        $this->url = $options['url'];
        $this->client = $this->initClient();
    }

    public function getAccessToken() {
        $response = Http::asForm()->post($this->url . "/oauth/token", [
            "grant_type" => "password",
            "client_id" => $this->client_id,
            "client_secret" => $this->secret,
            'username' => 'jesusant.guerrero@gmail.com',
            'password' => 'password',
            "scope" => "*",
        ]);

        return $response->json();
    }

    public function setTokens($body) {
        $this->accessToken = $body["access_token"];
        $this->tokenType =  "Bearer";
    }

    public function initClient() {
        $this->setTokens($this->getAccessToken());

        return new Client([
            "base_uri" => $this->url . "/",
            "headers" => [
                "Authorization" => "$this->tokenType ". $this->accessToken
            ]
        ]);
    }
}
