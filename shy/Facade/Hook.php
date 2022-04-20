<?php

namespace Shy\Facade;

use Shy\Facade;
use Shy\Hook as RealHook;

/**
 * Class DB
 * @package Shy\Facade
 *
 * @method static set(string $name, \Closure $closure)
 * @method static run(string $name, ...$param)
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
