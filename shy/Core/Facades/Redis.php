<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Libraries\Redis as realRedis;

class Redis extends facade
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
