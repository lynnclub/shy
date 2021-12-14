<?php

namespace Shy\Core;

use Shy\Core\Contract\Container as ContainerContract;
use Shy\Core\Exception\Container\ContainerException;
use Shy\Core\Exception\Container\NotFoundException;
use Closure;
use ReflectionClass;
use ReflectionFunction;
use Exception;
use LogicException;

class Container implements ContainerContract
{
    /**
     * @var string
     */
    protected $shyVersion = '2.0.0';

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
     * Instances
     *
     * @var array
     */
    protected $instances;

    /**
     * Memory used at make
     *
     * @var array
     */
    protected $memoryUsed;

    /**
     * @var bool
     */
    protected $intelligentScheduling = FALSE;

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
            static::$startTime = microtime(TRUE);
            static::$container = new static;
        }

        return static::$container;
    }

    /**
     * Shy version
     *
     * @return string
     */
    public function version()
    {
        return $this->shyVersion;
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
     * Add fork pid
     *
     * @param int $pid
     */
    public function addForkPid(int $pid)
    {
        static::$startId .= '_' . $pid;
        static::$startTime = microtime(TRUE);
    }

    /**
     * Set instance
     *
     * @param string $id
     * @param $instance
     *
     * @return Container
     */
    public function set(string $id, $instance)
    {
        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * @param array $sets
     */
    public function sets(array $sets)
    {
        foreach ($sets as $id => $instance) {
            $this->set($id, $instance);
        }
    }

    /**
     * Handle dependencies injection
     *
     * @param \ReflectionParameter[] $dependencies
     * @param array $parameters
     * @return array
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function handleDependencies(array $dependencies, array $parameters = [])
    {
        $results = [];

        foreach ($dependencies as $key => $dependency) {
            if ($dependency->isVariadic()) {
                $results = array_merge($results, array_slice($parameters, $key));
            } else {
                $results[$key] = null;

                if (isset($parameters[$key])) {
                    $results[$key] = $parameters[$key];
                } elseif (is_object($ReflectionClass = $dependency->getClass())) {
                    $className = $ReflectionClass->name;

                    if ($this->has($className)) {
                        $results[$key] = $this->get($className);
                    } elseif ($this->bound($className) || class_exists($className)) {
                        $results[$key] = $this->make($className);
                    }
                } elseif ($dependency->isDefaultValueAvailable()) {
                    $results[$key] = $dependency->getDefaultValue();
                }
            }
        }

        return $results;
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
            $parameters = $this->handleDependencies($constructor->getParameters(), $parameters);

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
     * @return mixed
     *
     * @throws NotFoundException
     * @throws \ReflectionException
     */
    public function runFunctionWithDependencyInjection($concrete, ...$parameters)
    {
        $reflector = new ReflectionFunction($concrete);
        $parameters = $this->handleDependencies($reflector->getParameters(), $parameters);

        return $concrete(...$parameters);
    }

    /**
     * Get closure for make
     *
     * @param $concrete
     *
     * @return Closure
     */
    protected function getClosure($concrete)
    {
        return function (...$parameters) use ($concrete) {
            $instance = null;

            if (is_string($concrete) && class_exists($concrete)) {
                $instance = $this->makeClassWithDependencyInjection($concrete, ...$parameters);
            } elseif ($concrete instanceof Closure) {
                $instance = $this->runFunctionWithDependencyInjection($concrete, ...$parameters);
            } else {
                $instance = $concrete;
            }

            return $instance;
        };
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

        $this->binds[$id] = $this->getClosure($concrete);

        return $this;
    }

    /**
     * @param array $binds
     * @return $this
     */
    public function binds(array $binds)
    {
        foreach ($binds as $id => $concrete) {
            $this->bind($id, $concrete);
        }

        return $this;
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
            return TRUE;
        }

        return FALSE;
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

        $memoryUsedBeforeMake = memory_get_usage();
        $this->instances[$id] = $this->binds[$id](...$parameters);
        $this->memoryUsed[$id] = memory_get_usage() - $memoryUsedBeforeMake;

        unset($this->binds[$id]);

        $this->intelligentScheduling && $this->record($id, 'make', ['params' => json_encode($parameters), 'memory' => $this->memoryUsed[$id]]);

        return $this->instances[$id];
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
        try {
            return $this->get($id);
        } catch (Exception $throwable) {
            if (isset($this->aliases[$id])) {
                $id = $this->aliases[$id];
            }

            return $this->make($id, $concrete, ...$parameters);
        }
    }

    /**
     * Set alias of instance id
     *
     * @param string $alias
     * @param string $id
     *
     * @return Container
     */
    public function alias(string $alias, string $id)
    {
        if ($alias === $id) {
            throw new LogicException("[{$id}] is aliased to itself.");
        }

        $this->aliases[$alias] = $id;

        return $this;
    }

    /**
     * @param array $aliases
     * @return $this
     */
    public function aliases(array $aliases)
    {
        foreach ($aliases as $alias => $id) {
            $this->alias($alias, $id);
        }

        return $this;
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
        } elseif (is_string($id)) {
            $this->intelligentScheduling && $this->record($id, 'remove');

            unset($this->binds[$id], $this->memoryUsed[$id], $this->instances[$id]);
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
        return $this->memoryUsed;
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
            $this->intelligentScheduling && $this->record($id, 'use');

            return $this->instances[$id];
        } elseif (isset($this->aliases[$id])) {
            $id = $this->aliases[$id];

            if (isset($this->instances[$id])) {
                $this->intelligentScheduling && $this->record($id, 'use');

                return $this->instances[$id];
            }
        }

        throw new NotFoundException('Instance id ' . $id . ' not found.');
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
    public function has(string $id): bool
    {
        if (isset($this->instances[$id])) {
            return TRUE;
        }

        if (isset($this->aliases[$id]) && isset($this->instances[$this->aliases[$id]])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean TRUE on success or FALSE on failure.
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

    /**
     * Intelligent scheduling
     */
    public function intelligentSchedulingOn()
    {
        $this->intelligentScheduling = TRUE;
    }

    /**
     * Record
     *
     * @param string $id
     * @param string $operation
     * @param array $params
     *
     * @return FALSE
     */
    protected function record(string $id, string $operation, array $params = [])
    {
        if (!isset($this->aliases['config']) || !isset($this->instances[$this->aliases['config']])) {
            return FALSE;
        }
        if (stripos($id, 'Shy\\') === 0) {
            return FALSE;
        }

        /**
         * Container ID
         * Instance ID
         * Operation
         * Timestamp
         */
        $structure = self::$startId . '^' . $id . '^' . $operation . '^' . time() . PHP_EOL;

        if (isset($this->aliases['request']) && isset($this->instances[$this->aliases['request']])) {
            $request = $this->instances[$this->aliases['request']];
            if ($request->isInitialized()) {
                $structure .= 'req^' . $request->getUrl() . '^' . implode(',', $request->getClientIps()) . PHP_EOL;
            }
        }

        $structure = 'ins^' . $structure . 'trc^' . json_encode(debug_backtrace()) . PHP_EOL;

        //Custom
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $structure .= $key . '^^' . json_encode($value);
                } else {
                    $structure .= $key . '^^' . $value;
                }
            }
            $structure .= PHP_EOL;
        }

        $dir = CACHE_PATH . 'log/' . (is_cli() ? 'command/container/' : 'web/container/');
        if (!is_dir($dir)) {
            mkdir($dir);
        }

        file_put_contents($dir . date('Ymd') . '.log', $structure . PHP_EOL, FILE_APPEND);
    }

}
