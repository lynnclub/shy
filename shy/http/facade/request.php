<?php

namespace shy\http\facade;

use shy\core\facade;
use shy\http\request as realRequest;

class request extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realRequest::class);
    }
}
