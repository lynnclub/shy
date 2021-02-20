<?php

namespace Shy\Core;

use RuntimeException;

abstract class Facade
{
    /**
     * Get Instance.
     *
     * @return mixed
     * @throws RuntimeException
     */
    protected static function getInstance()
    {
        throw new RuntimeException('Facade does not implement getInstance method.');
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

        if (method_exists($instance, $method)) {
            return $instance->$method(...$args);
        } else {
            return $instance::$method(...$args);
        }
    }
}
