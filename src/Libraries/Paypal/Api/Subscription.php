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

}
