<?php

/**
 * Container
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\core;

use Closure;
use RuntimeException;
use ReflectionClass;
use ReflectionException;

class container
{
    use exceptionHandlerRegister;

    /**
     * Bind ready to make
     *
     * @var mixed $binds
     */
    private static $binds;

    /**
     * Instances container
     *
     * @var mixed $instances
     */
    private static $instances;

    /**
     * Bind instance or closure ready to join container
     *
     * @param string $abstract
     * @param object $concrete
     * @throws RuntimeException
     * @return $this
     */
    public function bind(string $abstract, object $concrete)
    {
        if (
            $concrete instanceof Closure
            || is_object($concrete)
        ) {
            self::$binds[$abstract] = $concrete;
        } else {
            throw new RuntimeException('bind concrete type invalid');
        }

        return $this;
    }

    /**
     * Make new instance and join container
     *
     * @param string $abstract
     * @param object|string $concrete
     * @param array ...$parameters
     * @return object
     * @throws RuntimeException|ReflectionException
     */
    public function makeNew(string $abstract, $concrete = null, ...$parameters)
    {
        if (isset(self::$binds[$abstract])) {
            array_unshift($parameters, $concrete);
        } else {
            if (class_exists($abstract)) {
                array_unshift($parameters, $concrete);
                $concrete = $abstract;
            }

            if (empty($concrete)) {
                throw new RuntimeException('No concrete to make');
            } elseif (is_string($concrete) && class_exists($concrete)) {
                return $this->makeClassByReflection($abstract, $concrete, ...$parameters);
            }

            $this->bind($abstract, $concrete);
        }

        if (self::$binds[$abstract] instanceof Closure) {
            self::$instances[$abstract] = call_user_func(self::$binds[$abstract], ...$parameters);
        } elseif (is_object(self::$binds[$abstract])) {
            self::$instances[$abstract] = self::$binds[$abstract];
        }

        return self::$instances[$abstract];
    }

    /**
     * Get instance or make new
     *
     * @param string $abstract
     * @param object|string $concrete
     * @param array ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    public function getOrMakeNew(string $abstract, $concrete = null, ...$parameters)
    {
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        return $this->makeNew($abstract, $concrete, ...$parameters);
    }

    /**
     * Make class by reflection
     *
     * @param string $abstract
     * @param string $concrete
     * @param array ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    private function makeClassByReflection($abstract, $concrete, ...$parameters)
    {
        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new RuntimeException('class is not instantiable');
        }
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            self::$instances[$abstract] = $reflector->newInstanceWithoutConstructor();
        } else {
            self::$instances[$abstract] = $reflector->newInstanceArgs($parameters);
        }

        return self::$instances[$abstract];
    }

    /**
     * Clear bind and instance
     *
     * @param $abstract
     */
    public function clear($abstract)
    {
        unset(self::$binds[$abstract], self::$instances[$abstract]);
    }

    /**
     * Clear all binds and instances
     */
    public function clearAll()
    {
        self::$binds = [];
        self::$instances = [];
    }

    /**
     * Get the list of instances
     *
     * @return array
     */
    public function getList()
    {
        return array_keys(self::$instances);
    }

}