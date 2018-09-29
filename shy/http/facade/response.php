<?php

namespace shy\http\facade;

use shy\core\facade;

class response extends facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'response';
    }
}
