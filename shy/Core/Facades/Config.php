<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\Config as ConfigContract;

class Config extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(ConfigContract::class);
    }
}
