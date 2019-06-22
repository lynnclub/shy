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

    private $configDir = __DIR__ . '/../../config/';

    /**
     * Config
     *
     * @var mixed $config
     */
    private static $config;

    /**
     * Bind ready to join
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
     * Instances memory used
     *
     * @var array
     */
    private static $instancesMemoryUsed;

    private $beforeMakeInstanceMemoryUsed;

    /**
     * Set config
     *
     * @param string $abstract
     * @param $config
     * @return mixed
     */
    public function setConfig(string $abstract, $config)
    {
        if (isset(self::$config[$abstract])) {
            throw new RuntimeException('Config abstract ' . $abstract . ' exist and not empty.');
        }
        if (isset($config)) {
            self::$config[$abstract] = $config;
        } else {
            throw new RuntimeException('Config abstract ' . $abstract . ' config is null.');
        }

        return self::$config[$abstract];
    }

    /**
     * Remove config
     *
     * @param string $abstract
     */
    public function removeConfig(string $abstract)
    {
        unset(self::$config[$abstract]);
    }

    /**
     * Get config
     *
     * @param string $abstract
     * @param string $default
     * @return mixed
     */
    public function getConfig(string $abstract, $default = '')
    {
        if (isset(self::$config[$abstract])) {
            return self::$config[$abstract];
        }

        /**
         * autoload config file
         */
        $configFile = $this->configDir . $abstract . '.php';
        if (file_exists($configFile) && $config = require_file($configFile)) {
            return $this->setConfig($abstract, $config);
        }

        return $default;
    }

    /**
     * Is config exist
     *
     * @param string $abstract
     * @return bool
     */
    public function configExist(string $abstract)
    {
        if (isset(self::$config[$abstract])) {
            return true;
        }

        return false;
    }

    /**
     * Get all config
     *
     * @return mixed
     */
    public function getAllConfig()
    {
        return self::$config;
    }

    /**
     * Calc int in config
     *
     * @param string $abstract
     * @param int $int
     * @return mixed
     */
    public function configIntCalc(string $abstract, int $int = 1)
    {
        if (isset(self::$config[$abstract])) {
            if (!is_int(self::$config[$abstract])) {
                throw new RuntimeException('Config Int Calc need config value is int.');
            }
        } else {
            self::$config[$abstract] = 0;
        }

        self::$config[$abstract] += $int;

        return self::$config[$abstract];
    }

    /**
     * Bind instance or closure
     *
     * @param string $abstract
     * @param object|Closure $concrete
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
     * Make new instance and join to container
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
                $this->beforeMakeInstanceMemoryUsed = memory_get_usage();
                return $this->makeClassByReflection($abstract, $concrete, ...$parameters);
            }

            $this->bind($abstract, $concrete);
        }

        /**
         * Join
         */
        $this->beforeMakeInstanceMemoryUsed = memory_get_usage();
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
     * Make class by Reflection
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
            self::$instances[$abstract] = $reflector->newInstance(...$parameters);
        }
        $this->countMakeInstanceMemoryUsed($abstract);

        return self::$instances[$abstract];
    }

    /**
     * Count memory used while make instance
     *
     * @param $abstract
     */
    private function countMakeInstanceMemoryUsed(string $abstract)
    {
        if (!isset(self::$instancesMemoryUsed[$abstract])) {
            self::$instancesMemoryUsed[$abstract] = memory_get_usage() - $this->beforeMakeInstanceMemoryUsed;
        }
    }

    /**
     * Clear instance
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
     * Clear all instances
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
    public function inList(string $abstract)
    {
        if (isset(self::$instances[$abstract])) {
            return true;
        }

        return false;
    }

}
