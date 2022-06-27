<?php

namespace Shy\Facade;

use Shy\Contract\Config as ConfigContract;
use Shy\Facade;

/**
 * Class Config
 * @package Shy\Facade
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
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(ConfigContract::class);
    }
}
