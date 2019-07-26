<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\Cache as CacheContract;

class Cache extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(CacheContract::class);
    }
}
