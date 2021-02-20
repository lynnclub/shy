<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\Config as ConfigContract;

/**
 * Class Config
 * @package Shy\Core\Facades
 *
 * @method static array|false load(string $file)
 * @method static string|array|null find(string $key)
 * @method static bool set($key, $value, $ttl = null)
 * @method static mixed get($key, $default = null)
 * @method static bool delete($key)
 */
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
