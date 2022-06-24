<?php

namespace Shy\Facade;

use Shy\Facade;
use Shy\Contract\Cache as CacheContract;

/**
 * Class Cache
 * @package Shy\Facade
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
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(CacheContract::class);
    }
}
