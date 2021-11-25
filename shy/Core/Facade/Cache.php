<?php

namespace Shy\Core\Facade;

use Shy\Core\Facade;
use Shy\Core\Contract\Cache as CacheContract;

/**
 * Class Cache
 * @package Shy\Core\Facade
 *
 * @method static mixed connection($config_name = 'default')
 * @method static bool set($key, $value, $ttl = null)
 * @method static mixed get($key, $default = null)
 * @method static bool has($key)
 * @method static bool delete($key)
 */
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
