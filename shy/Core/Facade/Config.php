<?php

namespace Shy\Core\Facade;

use Shy\Core\Facade;
use Shy\Core\Contract\Config as ConfigContract;

/**
 * Class Config
 * @package Shy\Core\Facade
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
