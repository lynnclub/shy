<?php

namespace shy\core\facade;

use shy\core\facade;

class redis extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('shy_redis', 'shy\core\library\redis');
    }
}
