<?php namespace Insane\Treasurer\Libraries\Remote;

use GuzzleHttp\Client;
use Insane\Treasurer\Libraries\Remote\Api\Plan;
use Insane\Treasurer\Libraries\Remote\Api\Product;
use Insane\Treasurer\Libraries\Remote\Auth\ApiContext;
use Insane\Treasurer\Libraries\Remote\Api\Subscription;

class RemoteClient
{
    public Client $apiContext;
    public Product $product;
    public Plan $plan;
    public Subscription $subscription;

    public function __construct()
    {
        $this->client = new ApiContext(self::getSettings());
        $this->product = new Product($this->client);
        $this->plan = new Plan($this->client);
        $this->subscription = new Subscription($this->client);
    }

    public static function getSettings()
    {
        // Detect if we are running in live mode or sandbox
        $driver = config('treasurer.driver');
        $settings = [
            "url" => config("treasurer.drivers.{$driver}.url"),
            "client_id" => config("treasurer.drivers.{$driver}.client_id"),
            "secret" => config("treasurer.drivers.{$driver}.secret")
        ];
        return $settings;
    }
}
