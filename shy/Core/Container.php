<?php

namespace Shy\Core;

use Shy\Core\Contracts\Container as ContainerContract;
use Shy\Core\Exceptions\Container\Exception;
use Shy\Core\Exceptions\Container\NotFoundException;
use Closure;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionException;

class Container implements ContainerContract
{
    /**
     * @var Container
     */
    protected static $instance;

    /**
     * @var string
     */
    protected static $startId;

    /**
     * Binding ready to join container
     *
     * @var array
     */
    protected $binds;

    /**
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

    /**
     * Get container
     *
     * @return Container
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$startId = uniqid();
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set container
     *
     * @param ContainerContract|null $container
     * @return ContainerContract
     */
    public static function setInstance(ContainerContract $container = null)
    {
        static::$startId = uniqid();
        return static::$instance = $container;
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
     * Add forked process id to start id
     *
     * @param int $forkedPid
     */
    public function addForkedPidToStartId(int $forkedPid)
    {
        if (!strpos(static::$startId, '_')) {
            static::$startId .= '_' . $forkedPid;
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
                throw new Exception('Container: make id is empty');
            }

            if (is_string($concrete) && class_exists($concrete)) {
                $this->instances[$id] = $this->makeViaReflectionClass($concrete, ...$parameters);
            } elseif ($concrete instanceof Closure) {
                $this->instances[$id] = $this->runViaReflectionFunction($concrete, ...$parameters);
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
     * Get bound
     *
     * @param string $id
     *
     * @return string|\Closure|object|false
     */
    public function getBound(string $id)
    {
        if (isset($this->binds[$id])) {
            return $this->binds[$id];
        }

        return false;
    }

    /**
     * Make instance and join the container
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

    public function alias(string $alias, string $id)
    {
        if ($alias === $id) {
            throw new \LogicException("[{$id}] is aliased to itself.");
        }

        $this->aliases[$alias] = $id;
    }

    public function aliases(array $aliases)
    {
        foreach ($aliases as $alias => $id) {
            $this->alias($alias, $id);
        }
    }

    /**
     * Make instance via ReflectionClass
     *
     * @param string $concrete
     * @param array ...$parameters
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws ReflectionException
     *
     * @return object
     */
    public function makeViaReflectionClass(string $concrete, ...$parameters)
    {
        $reflector = new ReflectionClass($concrete);

        if (!$reflector->isInstantiable()) {
            throw new Exception('Container: class ' . $concrete . ' is not instantiable');
        }

        $constructor = $reflector->getConstructor();

        if (is_null($constructor)) {
            $instance = $reflector->newInstanceWithoutConstructor();
        } else {
            $parameters = $this->getDependencies($parameters, $constructor->getParameters());

            $instance = $reflector->newInstance(...$parameters);
        }

        return $instance;
    }

    /**
     * Run function via ReflectionFunction
     *
     * @param $concrete
     * @param array ...$parameters
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws ReflectionException
     *
     * @return mixed
     */
    public function runViaReflectionFunction($concrete, ...$parameters)
    {
        $reflector = new ReflectionFunction($concrete);
        $parameters = $this->getDependencies($parameters, $reflector->getParameters());

        return call_user_func($concrete, ...$parameters);
    }

    /**
     * Dependency injection.
     *
     * @param array $parameters
     * @param array $dependencies
     *
     * @return array
     */
    public function getDependencies(array $parameters, array $dependencies)
    {
        $results = [];

        foreach ($dependencies as $key => $dependency) {
            if (isset($parameters[$key])) {
                $results[] = $parameters[$key];
            } else {
                $results[] = is_null($dependency->getClass())
                    ? null
                    : $this->getOrMake($dependency->getClass()->name);
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
            throw new NotFoundException('Failed to find the instance via ID ' . $id . '.');
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
