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
     * Config dir
     *
     * @var string $configDir
     */
    private $configDir;

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
     * Container constructor.
     *
     * @param string $configDir
     */
    public function __construct(string $configDir = '')
    {
        $this->configDir = is_dir($configDir) ? $configDir : __DIR__ . '/../../config/';
    }

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
     * @param string|Closure|object $concrete
     * @throws RuntimeException
     * @return $this
     */
    public function bind(string $abstract, $concrete)
    {
        if (empty($abstract)) {
            throw new RuntimeException('Container: bind abstract' . $abstract . ' is empty');
        }
        if (empty($concrete)) {
            if (class_exists($abstract)) {
                $concrete = $abstract;
            } else {
                throw new RuntimeException('Container: bind concrete' . $concrete . ' is empty');
            }
        }

        if (
            $concrete instanceof Closure
            || is_object($concrete)
            || class_exists($concrete)
        ) {
            self::$binds[$abstract] = $concrete;
        } else {
            throw new RuntimeException('Container: bind concrete type invalid:' . $abstract);
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
            throw new RuntimeException('Container: make object abstract is empty');
        }
        /**
         * bind
         */
        if (!isset(self::$binds[$abstract])) {
            $this->bind($abstract, $concrete);
        }
        /**
         * Join
         */
        $this->beforeMakeInstanceMemoryUsed = memory_get_usage();
        if (is_string(self::$binds[$abstract]) && class_exists(self::$binds[$abstract])) {
            if (!class_exists($concrete)) {
                array_unshift($parameters, $concrete);
            }
            self::$instances[$abstract] = $this->makeClassByReflection(self::$binds[$abstract], ...$parameters);
        } elseif (self::$binds[$abstract] instanceof Closure) {
            if (!$concrete instanceof Closure) {
                array_unshift($parameters, $concrete);
            }
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
     * @param string $concrete
     * @param array ...$parameters
     * @return mixed
     * @throws ReflectionException
     */
    private function makeClassByReflection(string $concrete, ...$parameters)
    {
        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new RuntimeException('Container: class ' . $concrete . ' is not instantiable');
        }

        if (is_null($reflector->getConstructor())) {
            $instance = $reflector->newInstanceWithoutConstructor();
        } else {
            $instance = $reflector->newInstance(...$parameters);
        }

        return $instance;
    }

    /**
     * Count memory used while make instance
     *
     * @param $abstract
     */
    private function countMakeInstanceMemoryUsed(string $abstract)
    {
        self::$instancesMemoryUsed[$abstract][] = memory_get_usage() - $this->beforeMakeInstanceMemoryUsed;
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
