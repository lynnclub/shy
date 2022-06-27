<?php

namespace Shy;

use RuntimeException;

abstract class Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     *
     * @throws RuntimeException
     */
    protected static function getInstance()
    {
        throw new RuntimeException('getInstance() not implement.');
    }

    /**
     * 动态调用代理对象方法
     * Dynamically call proxy object methods.
     *
     * @param string $method
     * @param array $args
     * @return mixed
     *
     * @throws RuntimeException
     */
    public static function __callStatic(string $method, array $args)
    {
        $instance = static::getInstance();

        if (!$instance) {
            throw new RuntimeException('Can not get object.');
        }

        if (method_exists($instance, $method)) {
            return $instance->$method(...$args);
        } else {
            return $instance::$method(...$args);
        }
    }
}
