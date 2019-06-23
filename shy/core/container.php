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
    private $config;

    /**
     * Bind ready to join
     *
     * @var mixed $binds
     */
    private  $binds;

    /**
     * Instances container
     *
     * @var mixed $instances
     */
    private  $instances;

    /**
     * Instances memory used
     *
     * @var array
     */
    private $instancesMemoryUsed;

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
        if (isset($this->config[$abstract])) {
            throw new RuntimeException('Config abstract ' . $abstract . ' exist and not empty.');
        }
        if (isset($config)) {
            $this->config[$abstract] = $config;
        } else {
            throw new RuntimeException('Config abstract ' . $abstract . ' config is null.');
        }

        return $this->config[$abstract];
    }

    /**
     * Remove config
     *
     * @param string $abstract
     */
    public function removeConfig(string $abstract)
    {
        unset($this->config[$abstract]);
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
        if (isset($this->config[$abstract])) {
            return $this->config[$abstract];
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
        if (isset($this->config[$abstract])) {
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
        return $this->config;
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
        if (isset($this->config[$abstract])) {
            if (!is_int($this->config[$abstract])) {
                throw new RuntimeException('Config Int Calc need config value is int.');
            }
        } else {
            $this->config[$abstract] = 0;
        }

        $this->config[$abstract] += $int;

        return $this->config[$abstract];
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
            $this->binds[$abstract] = $concrete;
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
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
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
        if (!isset($this->binds[$abstract])) {
            $this->bind($abstract, $concrete);
        }
        /**
         * Join
         */
        $this->beforeMakeInstanceMemoryUsed = memory_get_usage();
        if (is_string($this->binds[$abstract]) && class_exists($this->binds[$abstract])) {
            if (!class_exists($concrete)) {
                array_unshift($parameters, $concrete);
            }
            $this->instances[$abstract] = $this->makeClassByReflection($this->binds[$abstract], ...$parameters);
        } elseif ($this->binds[$abstract] instanceof Closure) {
            if (!$concrete instanceof Closure) {
                array_unshift($parameters, $concrete);
            }
            $this->instances[$abstract] = call_user_func($this->binds[$abstract], ...$parameters);
        } elseif (is_object($this->binds[$abstract])) {
            $this->instances[$abstract] = $this->binds[$abstract];
        }
        unset($this->binds[$abstract]);
        $this->countMakeInstanceMemoryUsed($abstract);

        return $this->instances[$abstract];
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
        $this->instancesMemoryUsed[$abstract][] = memory_get_usage() - $this->beforeMakeInstanceMemoryUsed;
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
                unset($this->binds[$item], $this->instancesMemoryUsed[$item], $this->instances[$item]);
            }
        } else {
            unset($this->binds[$abstract], $this->instancesMemoryUsed[$abstract], $this->instances[$abstract]);
        }
    }

    /**
     * Clear all instances
     */
    public function clearAll()
    {
        $this->binds = [];
        $this->instancesMemoryUsed = [];
        $this->instances = [];
    }

    /**
     * Get the list of instances
     *
     * @return array
     */
    public function getList()
    {
        return array_keys($this->instances);
    }

    /**
     * Get the list of instances memory used
     *
     * @return array
     */
    public function getListMemoryUsed()
    {
        return $this->instancesMemoryUsed;
    }

    /**
     * Is in instances list
     *
     * @param string $abstract
     * @return bool
     */
    public function inList(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return true;
        }

        return false;
    }

}
