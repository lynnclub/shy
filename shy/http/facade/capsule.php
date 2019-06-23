<?php

namespace shy\http\facade;

use shy\core\facade;
use Illuminate\Database\Capsule\Manager;

class capsule extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(Manager::class);
    }
}
