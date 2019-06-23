<?php

namespace shy\http\facade;

use shy\core\facade;
use shy\http\response as shyResponse;

class response extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(shyResponse::class);
    }
}
