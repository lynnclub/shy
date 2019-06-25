<?php

namespace shy\core\facade;

use shy\core\facade;
use shy\core\library\redis as realRedis;

class redis extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realRedis::class);
    }
}
