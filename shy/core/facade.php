<?php

namespace shy\core;

use RuntimeException;

abstract class facade
{

    /**
     * Get the instance.
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected static function getInstance()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string $method
     * @param  array $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getInstance();

        if (!$instance) {
            throw new RuntimeException('Can not get facade object.');
        }

        return $instance->$method(...$args);
    }
}
