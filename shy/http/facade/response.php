<?php

namespace shy\http\facade;

use shy\core\facade;

class response extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('response');
    }
}
