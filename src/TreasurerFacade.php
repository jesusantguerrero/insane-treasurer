<?php

namespace Insane\Treasurer;

use Illuminate\Support\Facades\Facade;

class TreasurerFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'treasurer';
    }
}
