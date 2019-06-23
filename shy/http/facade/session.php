<?php

namespace shy\http\facade;

use shy\core\facade;
use shy\http\session as realSession;

class session extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realSession::class);
    }
}
