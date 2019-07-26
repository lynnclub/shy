<?php

namespace Shy\Core;

use Shy\Core\Contracts\Container as ContainerContract;
use Shy\Core\Exceptions\Container\ContainerException;
use Shy\Core\Exceptions\Container\NotFoundException;
use Closure;
use ReflectionClass;
use ReflectionFunction;
use InvalidArgumentException;
use LogicException;

class Container implements ContainerContract
{
    /**
     * @var Container
     */
    protected static $container;

    /**
     * @var string
     */
    protected static $startId;

    /**
     * @var string
     */
    protected static $startTime;

    /**
     * Binding ready to make
     *
     * @var array
     */
    protected $binds;

    /**
     * Alias of instance id
     *
     * @var array
     */
    protected $aliases;

    /**
     * @var array
     */
    protected $instances;

    /**
     * Memory used by instances
     *
     * @var array
     */
    protected $instancesMemory;

    protected $memoryUsedBeforeMakeInstance;

    private function __construct()
    {
        //
    }

    /**
     * Get container
     *
     * @return Container
     */
    public static function getContainer()
    {
        if (is_null(static::$container)) {
            static::$startId = uniqid();
            static::$startTime = microtime(true);
            static::$container = new static;
        }

        return static::$container;
    }

    /**
     * Get start id
     *
     * @return string
     */
    public function startId()
    {
        return static::$startId;
    }

    /**
     * Get start time
     *
     * @return string
     */
    public function startTime()
    {
        return static::$startTime;
    }

    /**
     * Add forked process id to start id
     *
     * @param int $forkedPid
     */
    public function addForkedPidToStartId(int $forkedPid)
    {
        if (!strpos(static::$startId, '_fork')) {
            static::$startId .= '_fork' . $forkedPid;
            static::$startTime = microtime(true);
        }
    }

    /**
     * Set instance
     *
     * @param string $id
     * @param $instance
     */
    public function set(string $id, $instance)
    {
        $this->instances[$id] = $instance;
    }

    /**
     * Set instances
     *
     * @param array $sets
     */
    public function sets(array $sets)
    {
        foreach ($sets as $id => $instance) {
            $this->set($id, $instance);
        }
    }

    /**
     * Bind ready to make
     *
     * @param string $id
     * @param $concrete
     *
     * @return Container
     */
    public function bind(string $id, $concrete = null)
    {
        if (is_null($concrete)) {
            $concrete = $id;
        }

        $this->binds[$id] = $this->getClosure($id, $concrete);

        return $this;
    }

    /**
     * Get closure for make
     *
     * @param string $id
     * @param $concrete
     *
     * @return Closure
     */
    protected function getClosure(string $id, $concrete)
    {
        return function (...$parameters) use ($id, $concrete) {
            if (empty($id)) {
                throw new InvalidArgumentException('making instance id is empty');
            }

            if (is_string($concrete) && class_exists($concrete)) {
                $this->instances[$id] = $this->makeClassWithDependencyInjection($concrete, ...$parameters);
            } elseif ($concrete instanceof Closure) {
                $this->instances[$id] = $this->runFunctionWithDependencyInjection($concrete, ...$parameters);
            } else {
                $this->instances[$id] = $concrete;
            }
        };
    }

    /**
     * @param array $binds
     */
    public function binds(array $binds)
    {
        foreach ($binds as $id => $concrete) {
            $this->bind($id, $concrete);
        }
    }

    /**
     * Is bound
     *
     * @param string $id
     *
     * @return bool
     */
    public function bound(string $id)
    {
        if (isset($this->binds[$id])) {
            return true;
        }

        return false;
    }

    /**
     * Make instance and join to container
     *
     * @param string $id
     * @param object|string|null $concrete
     * @param array ...$parameters
     *
     * @return mixed
     */
    public function make(string $id, $concrete = null, ...$parameters)
    {
        if (isset($this->binds[$id])) {
            array_unshift($parameters, $concrete);
        } else {
            $this->bind($id, $concrete);
        }

        $this->memoryUsedBeforeMakeInstance = memory_get_usage();

        $this->binds[$id](...$parameters);
        unset($this->binds[$id]);

        $this->countMemoryUsedToNewInstance($id);
        //$this->instancesRecord($id, 'make', ['params' => json_encode($parameters), 'memory' => end($this->instancesMemory[$id])]);

        return $this->instances[$id];
    }

    /**
     * @param array $makes
     */
    public function makes(array $makes)
    {
        foreach ($makes as $id => $concrete) {
            $this->make($id, $concrete);
        }
    }

    /**
     * Set alias of instance id
     *
     * @param string $alias
     * @param string $id
     */
    public function alias(string $alias, string $id)
    {
        if ($alias === $id) {
            throw new LogicException("[{$id}] is aliased to itself.");
        }

        $this->aliases[$alias] = $id;
    }

    /**
     * @param array $aliases
     */
    public function aliases(array $aliases)
    {
        foreach ($aliases as $alias => $id) {
            $this->alias($alias, $id);
        }
    }

    /**
     * Make an instance with dependency injection
     *
     * @param string $concrete
     * @param array ...$parameters
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     *
     * @return object
     */
    public function makeClassWithDependencyInjection(string $concrete, ...$parameters)
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new ContainerException('Class ' . $concrete . ' is not instantiable');
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            $instance = $reflector->newInstanceWithoutConstructor();
        } else {
            $parameters = $this->getOrMakeDependencies($parameters, $constructor->getParameters());

            $instance = $reflector->newInstance(...$parameters);
        }

        return $instance;
    }

    /**
     * Run function with dependency injection
     *
     * @param $concrete
     * @param array ...$parameters
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function runFunctionWithDependencyInjection($concrete, ...$parameters)
    {
        $reflector = new ReflectionFunction($concrete);
        $parameters = $this->getOrMakeDependencies($parameters, $reflector->getParameters());

        return call_user_func($concrete, ...$parameters);
    }

    /**
     * Get or make dependency object
     *
     * @param array $parameters
     * @param \ReflectionParameter[] $dependencies
     *
     * @return array
     *
     * @throws NotFoundException
     */
    public function getOrMakeDependencies(array $parameters, array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $key => $dependency) {
            $results[$key] = null;

            if (isset($parameters[$key])) {
                $results[$key] = $parameters[$key];
            } elseif (!is_null($class = $dependency->getClass())) {
                $className = $class->name;

                if ($this->bound($className)) {
                    $results[$key] = $this->make($className);
                } elseif ($this->has($className)) {
                    $results[$key] = $this->get($className);
                } elseif ($dependency->isDefaultValueAvailable()) {
                    $results[$key] = $dependency->getDefaultValue();
                } elseif (class_exists($className)) {
                    $results[$key] = $this->make($className);
                }
            }
        }

        return $results;
    }

    /**
     * Get or make instance
     *
     * @param string $id
     * @param object|string|null $concrete
     * @param array ...$parameters
     *
     * @return object
     */
    public function getOrMake(string $id, $concrete = null, ...$parameters)
    {
        if (isset($this->instances[$id])) {
            //$this->instancesRecord($id, 'use');

            return $this->instances[$id];
        } elseif (isset($this->aliases[$id])) {
            if (isset($this->instances[$this->aliases[$id]])) {
                return $this->instances[$this->aliases[$id]];
            } else {
                $id = $this->aliases[$id];
            }
        }

        return $this->make($id, $concrete, ...$parameters);
    }

    /**
     * Count the memory used to create the instance
     *
     * @param $id
     */
    protected function countMemoryUsedToNewInstance(string $id)
    {
        $this->instancesMemory[$id][] = memory_get_usage() - $this->memoryUsedBeforeMakeInstance;
    }

    /**
     * Remove bind and instance
     *
     * @param string|array $id
     */
    public function remove($id)
    {
        if (is_array($id)) {
            foreach ($id as $item) {
                $this->remove($item);
            }
        } else {
            //$this->instancesRecord($id, 'clear');

            unset($this->binds[$id], $this->instancesMemory[$id], $this->instances[$id]);
        }
    }

    /**
     * Get the list of instances id
     *
     * @return array
     */
    public function list()
    {
        return array_keys($this->instances);
    }

    /**
     * Get the list of instances memory used
     *
     * @return array
     */
    public function memoryUsed()
    {
        return $this->instancesMemory;
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundException  No entry was found for **this** identifier.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (isset($this->instances[$id])) {
            //$this->instancesRecord($id, 'use');

            return $this->instances[$id];
        } elseif (isset($this->aliases[$id]) && isset($this->instances[$this->aliases[$id]])) {
            return $this->instances[$this->aliases[$id]];
        } else {
            throw new NotFoundException('instance id ' . $id . ' not found.');
        }
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        if (isset($this->instances[$id])) {
            return true;
        }

        if (isset($this->aliases[$id]) && isset($this->instances[$this->aliases[$id]])) {
            return true;
        }

        return false;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     *
     * @since 5.0.0
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     *
     * @throws \Psr\Container\NotFoundExceptionInterface  No entry was found for **this** identifier.
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->getOrMake($offset);
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     *
     * @return void
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     *
     * @return void
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count()
    {
        return count($this->instances);
    }

//    /**
//     * Instances record
//     *
//     * data format: key1 ^^ value1 ... ^^ key2 ^^ value2 ...
//     *
//     * start id ^^ class id ^^ operation ^^ time ^^ isCli ^^ url ^^ ips ^^ trace ...customer params
//     *
//     * @todo Records data for instances intelligent scheduling
//     *
//     * @param string $id
//     * @param string $operation
//     * @param array $params
//     * @return bool
//     */
//    private function instancesRecord(string $id, string $operation, array $params = [])
//    {
//        if (!config_key('switch', 'instances_scheduling')) {
//            return false;
//        }
//        if (in_array($id, config_key('avoid_list', 'instances_scheduling'))) {
//            return false;
//        }
//
//        $structure = [
//            'startId' => $this->startId,
//            'id' => $id,
//            'operation' => $operation,
//            'time' => time(),
//            'isCli' => $this->getConfig('IS_CLI'),
//            'url' => '',
//            'ips' => '',
//            'trace' => ''
//        ];
//
//        $request = shy(request::class);
//        if ($request->isInit()) {
//            $structure['url'] = $request->getUrl();
//            $structure['ips'] = implode(',', $request->getClientIps());
//        }
//
//        $structure['trace'] = json_encode(debug_backtrace());
//
//        if (!empty($params)) {
//            foreach ($params as $key => $value) {
//                if (is_array($value)) {
//                    $structure[$key] = json_encode($value);
//                } else {
//                    $structure[$key] = $value;
//                }
//            }
//        }
//
//        $finalStructure = [];
//        foreach ($structure as $key => $value) {
//            $finalStructure[] = $key;
//            $finalStructure[] = $value;
//        }
//
//        file_put_contents(config_key('cache', 'path') . 'instances_record/' . date('Ymd') . '.log', implode('^^', $finalStructure) . PHP_EOL, FILE_APPEND);
//
//    }

}
