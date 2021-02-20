<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Hook as RealHook;

/**
 * Class DB
 * @package Shy\Core\Facades
 *
 * @method static set(string $name, \Closure $closure)
 * @method static run(string $name)
 */
class Hook extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(RealHook::class);
    }
}
