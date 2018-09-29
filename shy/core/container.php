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
     * Bind prepare to make
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
     * Bind object or closure to make object.
     *
     * @param string $abstract
     * @param object $concrete
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
     * Bind object And Return
     *
     * @param string $abstract
     * @param object $concrete
     * @return mixed
     */
    public function bindObjectAndReturn(string $abstract, object $concrete)
    {
        if (is_object($concrete)) {
            self::$binds[$abstract] = self::$instances[$abstract] = $concrete;
        } else {
            throw new RuntimeException('bind concrete type invalid');
        }

        return self::$binds[$abstract];
    }

    /**
     * Make object instance after bind
     *
     * @param string $abstract
     * @param object|string $concrete
     * @param array ...$parameters
     * @return mixed
     * @throws RuntimeException|ReflectionException
     */
    public function make(string $abstract, $concrete = null, ...$parameters)
    {
        if (isset(self::$instances[$abstract])) {
            return self::$instances[$abstract];
        }

        if (isset(self::$binds[$abstract])) {
            array_push($parameters, $concrete);
        } else {
            if (class_exists($abstract)) {
                array_unshift($parameters, $concrete);
                return $this->makeClassByReflection($abstract, ...$parameters);
            }
            if (empty($concrete)) {
                throw new RuntimeException('No concrete to make');
            } else {
                $this->bind($abstract, $concrete);
            }
        }

        if (self::$binds[$abstract] instanceof Closure) {
            self::$instances[$abstract] = call_user_func(self::$binds[$abstract], ...$parameters);
        } elseif (is_object(self::$binds[$abstract])) {
            self::$instances[$abstract] = self::$binds[$abstract];
        }

        return self::$instances[$abstract];
    }

    /**
     * Make Class By Reflection
     *
     * @param $abstract
     * @param array ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    private function makeClassByReflection($abstract, ...$parameters)
    {
        $reflector = new ReflectionClass($abstract);
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
     * Clear Bind And Instance
     *
     * @param $abstract
     */
    public function clear($abstract)
    {
        unset(self::$binds[$abstract], self::$instances[$abstract]);
    }

    /**
     * Clear All Binds And Instances
     */
    public function clearAll()
    {
        self::$binds = [];
        self::$instances = [];
    }

    /**
     * Get Instances List
     *
     * @return array
     */
    public function getList()
    {
        return array_keys(self::$instances);
    }

}