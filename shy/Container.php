<?php

namespace Shy;

use Closure;
use Exception;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;
use Shy\Contract\Container as ContainerContract;
use Shy\Exception\Container\ContainerException;
use Shy\Exception\Container\NotFoundException;

class Container implements ContainerContract
{
    /**
     * 害羞框架版本
     * The Shy Framework version
     *
     * @var string
     */
    protected $shyVersion = '2.1.0';

    /**
     * 容器实例
     * Container instance
     *
     * @var Container
     */
    protected static $container;

    /**
     * 容器启动id
     * Container start id
     *
     * @var string
     */
    protected static $startId;

    /**
     * 容器启动时间
     * Container start time
     *
     * @var string
     */
    protected static $startTime;

    /**
     * 用于制作的材料
     * Materials for make
     *
     * @var array
     */
    protected $binds;

    /**
     * 实例id的别名
     * Alias for instance id
     *
     * @var array
     */
    protected $aliases;

    /**
     * 实例池
     * Instance pool
     *
     * @var array
     */
    protected $instances;

    /**
     * 制作时的内存消耗记录
     * Memory consumption record when making
     *
     * @var array
     */
    protected $memoryUsed;

    /**
     * 是否开启实例智能调度
     * Whether to enable instance intelligent scheduling
     *
     * @var bool
     */
    protected $intelligentScheduling = FALSE;

    private function __construct()
    {
        // 禁止外部实例化
        // disallow external instantiation
    }

    /**
     * 获取容器
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
     * 获取害羞框架版本
     * Get the Shy Framework version
     *
     * @return string
     */
    public function version()
    {
        return $this->shyVersion;
    }

    /**
     * 获取容器启动id
     * Get container start id
     *
     * @return string
     */
    public function startId()
    {
        return static::$startId;
    }

    /**
     * 获取容器启动时间
     * Get container start time
     *
     * @return string
     */
    public function startTime()
    {
        return static::$startTime;
    }

    /**
     * 更新派生进程的启动信息
     * Update forked process start information
     *
     * @param int $pid
     */
    public function updateForkedProcessStartInfo(int $pid)
    {
        static::$startId .= '_' . $pid;
        static::$startTime = microtime(TRUE);
    }

    /**
     * 设置实例到容器池
     * Set instance to container pool
     *
     * @param string $id
     * @param $instance
     * @return Container
     */
    public function set(string $id, $instance)
    {
        $this->instances[$id] = $instance;

        return $this;
    }

    /**
     * 批量设置实例到容器池
     * Batch set instances to container pool
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
     * 处理依赖注入
     * Handle dependency injection
     *
     * @param ReflectionParameter[] $dependencies
     * @param array $parameters
     * @return array
     *
     * @throws NotFoundException
     */
    public function handleDI(array $dependencies, array $parameters = [])
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
                    } elseif ($this->isBound($className) || class_exists($className)) {
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
     * 通过反射制作实例，支持依赖注入
     * Make an instance by reflection, support dependency injection
     *
     * @param string $concrete
     * @param ...$parameters
     * @return object
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function makeInstanceWithDI(string $concrete, ...$parameters)
    {
        $reflector = new ReflectionClass($concrete);
        if (!$reflector->isInstantiable()) {
            throw new ContainerException('Class ' . $concrete . ' is not instantiable');
        }

        $constructor = $reflector->getConstructor();
        if (is_null($constructor)) {
            $instance = $reflector->newInstanceWithoutConstructor();
        } else {
            $parameters = $this->handleDI($constructor->getParameters(), $parameters);

            $instance = $reflector->newInstance(...$parameters);
        }

        return $instance;
    }

    /**
     * 执行匿名函数，支持依赖注入
     * Execute anonymous functions, support dependency injection
     *
     * @param $concrete
     * @param ...$parameters
     * @return mixed
     *
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function executeFunctionWithDI($concrete, ...$parameters)
    {
        $reflector = new ReflectionFunction($concrete);
        $parameters = $this->handleDI($reflector->getParameters(), $parameters);

        return $concrete(...$parameters);
    }

    /**
     * 为制作获取闭包
     * Get closure for make
     *
     * @param $concrete
     * @return Closure
     */
    protected function getClosureForMake($concrete)
    {
        return function (...$parameters) use ($concrete) {
            if (is_string($concrete) && class_exists($concrete)) {
                $instance = $this->makeInstanceWithDI($concrete, ...$parameters);
            } elseif ($concrete instanceof Closure) {
                $instance = $this->executeFunctionWithDI($concrete, ...$parameters);
            } else {
                $instance = $concrete;
            }

            return $instance;
        };
    }

    /**
     * 绑定用于制作的材料
     * Bind materials for make
     *
     * @param string $id
     * @param $concrete
     * @return $this
     */
    public function bind(string $id, $concrete = null)
    {
        if (is_null($concrete)) {
            $concrete = $id;
        }

        $this->binds[$id] = $this->getClosureForMake($concrete);

        return $this;
    }

    /**
     * 批量绑定用于制作的材料
     * Batch bind materials for make
     *
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
     * 是否已绑定
     * Is bound
     *
     * @param string $id
     * @return bool
     */
    public function isBound(string $id)
    {
        if (isset($this->binds[$id])) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * 制作实例，并加入容器
     * Make an instance and join to the container
     *
     * @param string $id
     * @param null $concrete
     * @param ...$parameters
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
     * 获取或制作实例
     * Get or make instance
     *
     * @param string $id
     * @param null $concrete
     * @param ...$parameters
     * @return mixed|object
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
     * 设置实例id的别名
     * Set alias for instance id
     *
     * @param string $alias
     * @param string $id
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
     * 批量设置实例id的别名
     * Batch set alias for instance id
     *
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
     * 从容器中移除绑定材料与实例
     * Remove bound materials and instances from the container
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
     * 获取容器中的实例id列表
     * Get a list of instance ids in the container
     *
     * @return array
     */
    public function list()
    {
        return array_keys($this->instances);
    }

    /**
     * 获取制作时的内存消耗记录
     * Get the memory consumption record when making
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
     * @return mixed Entry.
     * @throws NotFoundException  No entry was found for **this** identifier.
     */
    public function get(string $id)
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
     * 开启实例智能调度
     * Enable instance intelligent scheduling
     */
    public function enableIntelligentScheduling()
    {
        $this->intelligentScheduling = TRUE;
    }

    /**
     * 实例记录
     * Instance record
     *
     * @param string $id
     * @param string $operation
     * @param array $params
     * @return bool
     */
    protected function record(string $id, string $operation, array $params = [])
    {
        if (!isset($this->aliases['config']) || !isset($this->instances[$this->aliases['config']])) {
            return FALSE;
        }

        /**
         * 数据结构
         *
         * 1. 容器启动id Container start id
         * 2. 实例id Instance ID
         * 3. 操作类型 Operation
         * 4. 时间戳 Timestamp
         */
        $structure = self::$startId . '^' . $id . '^' . $operation . '^' . time() . PHP_EOL;

        if (isset($this->aliases['request']) && isset($this->instances[$this->aliases['request']])) {
            $request = $this->instances[$this->aliases['request']];
            if ($request->isInitialized()) {
                $structure .= 'req^'
                    . $request->getUri() . '^'
                    . implode(',', $request->getClientIps()) . PHP_EOL;
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

        return TRUE;
    }
}
