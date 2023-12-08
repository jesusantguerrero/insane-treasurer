<?php namespace Insane\Treasurer\Libraries\Paypal;

use GuzzleHttp\Client;
use Insane\Treasurer\Libraries\Paypal\Api\Plan;
use Insane\Treasurer\Libraries\Paypal\Api\Product;
use Insane\Treasurer\Libraries\Paypal\Auth\ApiContext;
use Insane\Treasurer\Libraries\Paypal\Api\Subscription;

class PaypalClient
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
        $mode = config('treasurer.settings.mode') == 'live' ? 'live' : 'sandbox';
        $driver = config('treasurer.driver');
        $settings = [
            "client_id" => config("treasurer.drivers.{$driver}.{$mode}_client_id"),
            "secret" => config("treasurer.drivers.{$driver}.{$mode}_secret")
        ];
        return $settings;
    }
}
