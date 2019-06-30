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
use shy\http\request;
use Exception;

class container
{
    use exceptionHandlerRegister;

    /**
     * Start id
     *
     * @var string $startId
     */
    private $startId;

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
    private $binds;

    /**
     * Instances container
     *
     * @var mixed $instances
     */
    private $instances;

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
        $this->startId = uniqid();
        $this->setConfig('SHY_START_TIME', microtime(true));
        $this->configDir = is_dir($configDir) ? $configDir : __DIR__ . '/../../config/' . ENVIRONMENT . DIRECTORY_SEPARATOR;
    }

    /**
     * Get start id
     *
     * @return string
     */
    public function getStartId()
    {
        return $this->startId;
    }

    /**
     * Fork process no add to start id
     *
     * @param int $no
     */
    public function forkProcessNoAddToStartId(int $no)
    {
        if (!strpos($this->startId, '_')) {
            $this->startId .= '_' . $no;
        }
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
            throw new RuntimeException('Container: binding abstract' . $abstract . ' is empty.');
        }
        if (is_array($concrete)) {
            $concrete = null;
        }

        if ($concrete instanceof Closure
            || is_object($concrete)
            || class_exists($concrete)
        ) {
            $this->binds[$abstract] = $concrete;
        } else {
            if (class_exists($abstract)) {
                $this->binds[$abstract] = $abstract;
            } else {
                if (empty($concrete)) {
                    throw new RuntimeException('Container: binding concrete' . $concrete . ' is empty.');
                } else {
                    throw new RuntimeException('Container: abstract ' . $abstract . ' binding concrete type invalid.');
                }
            }
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
            $this->instancesRecord($abstract, 'use');
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
        if ($this->binds[$abstract] instanceof Closure) {
            if (!$concrete instanceof Closure) {
                array_unshift($parameters, $concrete);
            }
            $this->instances[$abstract] = call_user_func($this->binds[$abstract], ...$parameters);
        } elseif (is_object($this->binds[$abstract])) {
            $this->instances[$abstract] = $this->binds[$abstract];
        } elseif (is_string($this->binds[$abstract]) && class_exists($this->binds[$abstract])) {
            if (!is_string($concrete) || !class_exists($concrete)) {
                array_unshift($parameters, $concrete);
            }
            $this->instances[$abstract] = $this->makeClassByReflection($this->binds[$abstract], ...$parameters);
        }
        unset($this->binds[$abstract]);
        $this->countMakeInstanceMemoryUsed($abstract);
        $this->instancesRecord($abstract, 'make', ['params' => json_encode($parameters), 'memory' => end($this->instancesMemoryUsed[$abstract])]);

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
                $this->instancesRecord($item, 'clear');
                unset($this->binds[$item], $this->instancesMemoryUsed[$item], $this->instances[$item]);
            }
        } else {
            $this->instancesRecord($abstract, 'clear');
            unset($this->binds[$abstract], $this->instancesMemoryUsed[$abstract], $this->instances[$abstract]);
        }
    }

    /**
     * Instances record
     *
     * data format: key1 ^^ value1 ... ^^ key2 ^^ value2 ...
     *
     * start id ^^ class abstract ^^ operation ^^ time ^^ isCli ^^ url ^^ ips ^^ trace ...customer params
     *
     * @todo Records data for instances intelligent scheduling
     *
     * @param string $abstract
     * @param string $operation
     * @param array $params
     * @return bool
     */
    private function instancesRecord(string $abstract, string $operation, array $params = [])
    {
        if (!config_key('switch', 'instances_scheduling')) {
            return false;
        }
        if (in_array($abstract, config_key('avoid_list', 'instances_scheduling'))) {
            return false;
        }

        $structure = [
            'startId' => $this->startId,
            'abstract' => $abstract,
            'operation' => $operation,
            'time' => time(),
            'isCli' => $this->getConfig('IS_CLI'),
            'url' => '',
            'ips' => '',
            'trace' => ''
        ];

        $request = shy(request::class);
        if ($request->isInit()) {
            $structure['url'] = $request->getUrl();
            $structure['ips'] = implode(',', $request->getClientIps());
        }

        $structure['trace'] = json_encode(debug_backtrace());

        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    $structure[$key] = json_encode($value);
                } else {
                    $structure[$key] = $value;
                }
            }
        }

        $finalStructure = [];
        foreach ($structure as $key => $value) {
            $finalStructure[] = $key;
            $finalStructure[] = $value;
        }

        file_put_contents(config_key('cache', 'path') . 'instances_record/' . date('Ymd') . '.log', implode('^^', $finalStructure) . PHP_EOL, FILE_APPEND);

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
     * @return object|bool
     */
    public function inList(string $abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        return false;
    }

}
