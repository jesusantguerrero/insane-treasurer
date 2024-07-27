<?php
namespace Insane\Treasurer\Libraries\Remote\Api;

use Insane\Treasurer\Libraries\Remote\Auth\ApiContext;

class Plan {
    private const ENDPOINT = "api/subscription-plans";
    use ApiBehavior;

    public function __construct( ApiContext $apiContext)
    {
        $this->apiContext = $apiContext;
        $this->endpoint = self::ENDPOINT;
        $this->resultName = "plans";
    }
}
