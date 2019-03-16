<?php

namespace app\http\facade;

use shy\core\facade;

class example extends facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'example';
    }
}
