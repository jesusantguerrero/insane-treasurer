<?php
namespace Insane\Treasurer\Libraries\Paypal\Api;

use Insane\Treasurer\Libraries\Paypal\Auth\ApiContext;

class Plan {
    private const ENDPOINT = "billing/plans";
    use ApiBehavior;

    public function __construct( ApiContext $apiContext)
    {
        $this->apiContext = $apiContext;
        $this->endpoint = self::ENDPOINT;
        $this->resultName = "plans";
    }
}
