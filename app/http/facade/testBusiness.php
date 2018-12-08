<?php

namespace app\http\facade;

use shy\core\facade;

class testBusiness extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('app\http\business\testBusiness');
    }
}
