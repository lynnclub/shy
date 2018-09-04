<?php

namespace shy\facade;

use shy\core\facade;

class web extends facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'web';
    }
}
