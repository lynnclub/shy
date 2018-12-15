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
     * Bind Ready to Join
     *
     * @var mixed $binds
     */
    private static $binds;

    /**
     * Instances Container
     *
     * @var mixed $instances
     */
    private static $instances;

    /**
     * Instances Memory Used
     *
     * @var array
     */
    private static $instancesMemoryUsed;

    private $beforeMakeInstanceMemoryUsed;

    /**
     * Bind Instance or Closure
     *
     * @param string $abstract
     * @param object $concrete
     * @throws RuntimeException
     * @return $this
     */
    public function bind(string $abstract, $concrete)
    {
        if (
            $concrete instanceof Closure
            || is_object($concrete)
        ) {
            self::$binds[$abstract] = $concrete;
        } else {
            throw new RuntimeException('Bind concrete type invalid:' . $abstract);
        }

        return $this;
    }

    /**
     * Make New Instance and Join to Container
     *
     * @param string $abstract
     * @param object|string $concrete
     * @param array ...$parameters
     * @return object
     * @throws RuntimeException|ReflectionException
     */
    public function makeNew(string $abstract, $concrete = null, ...$parameters)
    {
        if (empty($abstract)) {
            throw new RuntimeException('Abstract is empty');
        }

        $this->beforeMakeInstanceMemoryUsed = memory_get_usage() / 1024;
        if (isset(self::$binds[$abstract])) {
            array_unshift($parameters, $concrete);
        } else {
            /**
             * abstract is namespace
             */
            if (class_exists($abstract)) {
                array_unshift($parameters, $concrete);
                $concrete = $abstract;
            }
            /**
             * concrete is namespace
             */
            if (empty($concrete)) {
                throw new RuntimeException('No concrete to make');
            } elseif (is_string($concrete) && class_exists($concrete)) {
                return $this->makeClassByReflection($abstract, $concrete, ...$parameters);
            }

            $this->bind($abstract, $concrete);
        }

        /**
         * Join
         */
        if (self::$binds[$abstract] instanceof Closure) {
            self::$instances[$abstract] = call_user_func(self::$binds[$abstract], ...$parameters);
        } elseif (is_object(self::$binds[$abstract])) {
            self::$instances[$abstract] = self::$binds[$abstract];
        }
        unset(self::$binds[$abstract]);
        $this->countMakeInstanceMemoryUsed($abstract);

        return self::$instances[$abstract];
    }

    /**
     * Get Instance or Make New
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
     * Make Class by Reflection
     *
     * @param string $abstract
     * @param string $concrete
     * @param array ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    private function makeClassByReflection(string $abstract, string $concrete, ...$parameters)
    {
        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new RuntimeException('class ' . $concrete . ' is not instantiable');
        }
        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            self::$instances[$abstract] = $reflector->newInstanceWithoutConstructor();
        } else {
            self::$instances[$abstract] = $reflector->newInstanceArgs($parameters);
        }
        $this->countMakeInstanceMemoryUsed($abstract);

        return self::$instances[$abstract];
    }

    /**
     * Count Make Instance Memory Used
     *
     * @param $abstract
     */
    private function countMakeInstanceMemoryUsed($abstract)
    {
        self::$instancesMemoryUsed[$abstract] = (memory_get_usage() / 1024) - $this->beforeMakeInstanceMemoryUsed;
    }

    /**
     * Clear Instance
     *
     * @param string|array $abstract
     */
    public function clear($abstract)
    {
        if (is_array($abstract)) {
            foreach ($abstract as $item) {
                unset(self::$binds[$item], self::$instancesMemoryUsed[$item], self::$instances[$item]);
            }
        } else {
            unset(self::$binds[$abstract], self::$instancesMemoryUsed[$abstract], self::$instances[$abstract]);
        }
    }

    /**
     * Clear All Instances
     */
    public function clearAll()
    {
        self::$binds = [];
        self::$instancesMemoryUsed = [];
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

    /**
     * Get the list of instances memory used
     *
     * @return array
     */
    public function getListMemoryUsed()
    {
        return self::$instancesMemoryUsed;
    }

    /**
     * Is in instances list
     *
     * @param string $abstract
     * @return bool
     */
    public function inList($abstract)
    {
        if (isset(self::$instances[$abstract])) {
            return true;
        }

        return false;
    }

}