<?php

namespace Insane\Treasurer\Services;

use Insane\Treasurer\Contracts\BillableEntity;

class BillableService {
    public function __construct(private BillableEntity $biller)
    {
        
    }

    public function check($callback) {
        $biller = $this->biller->resolve(request());
        return $callback($biller, $biller->subscription->plan);
    }
}
