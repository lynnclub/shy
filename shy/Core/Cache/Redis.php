<?php

namespace Shy\Core\Cache;

use Exception;
use Redis as PhpRedis;
use Shy\Core\Contract\Cache as CacheContracts;
use Shy\Core\Contract\DataBase;
use Shy\Core\Exception\Cache\InvalidArgumentException;

class Redis extends PhpRedis implements CacheContracts, DataBase
{
    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @var Redis []
     */
    protected $connections = [];

    /**
     * Redis constructor.
     *
     * @param string $config_name
     * @throws Exception
     */
    public function __construct($config_name = 'default')
    {
        parent::__construct();

        $this->connection($config_name);
    }

    /**
     * Add connection
     *
     * @param string $config_name
     * @param array $config
     */
    public function addConnection(string $config_name, array $config)
    {
        $this->configs[$config_name] = $config;
    }

    /**
     * Connection
     *
     * @param string $config_name
     * @throws Exception
     * @return Redis
     */
    public function connection($config_name = 'default')
    {
        if (isset($this->connections[$config_name])) {
            try {
                if ($this->connections[$config_name]->ping()) {
                    return $this->connections[$config_name];
                }
            } catch (\Throwable $throwable) {
                unset($this->connections[$config_name]);
            }
        }

        if (empty($this->configs)) {
            $this->configs = array_merge(config('cache.redis'), $this->configs);
        }

        if (!isset($this->configs[$config_name])) {
            throw new Exception('Redis Config ' . $config_name . ' not set');
        }
        $config = $this->configs[$config_name];
        if (!isset($config['host'], $config['port'])) {
            throw new Exception('Redis Config ' . $config_name . ' no host or port');
        }

        if ($config_name == 'default') {
            $redis = $this;
        } else {
            $redis = new self();
        }

        if (!$redis->connect($config['host'], $config['port'])) {
            throw new Exception('Redis Config ' . $config_name . ' connect failed');
        }

        if (!empty($config['password'])) {
            if (!$redis->auth($config['password'])) {
                throw new Exception('Redis Config ' . $config_name . ' auth failed');
            }
        }

        if (!$redis->ping()) {
            throw new Exception('Redis Config ' . $config_name . ' connect failed');
        }

        if (isset($config['database'])) {
            if (!$redis->select($config['database'])) {
                throw new Exception('Redis Config ' . $config_name . ' db ' . $config['database'] . ' select failed');
            }
        }

        return $this->connections[$config_name] = $redis;
    }

    /**
     * Fetches a value from the cache.
     *
     * @param string $key The unique key of this item in the cache.
     * @param mixed $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Key is empty.');
        }
        if (!is_string($key)) {
            throw new InvalidArgumentException('key is not a string.');
        }

        if ($value = parent::get($key)) {
            return $value;
        }

        return $default;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and FALSE on failure.
     */
    public function clear()
    {
        return $this->flushDB();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param array $keys A list of keys that can obtained in a single operation.
     * @param mixed $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple(array $keys = null, $default = null)
    {
        if (empty($keys)) {
            throw new InvalidArgumentException('Keys is empty.');
        }
        if (!is_array($keys)) {
            throw new InvalidArgumentException('keys is not a array.');
        }

        if ($values = parent::getMultiple($keys)) {
            return $values;
        }

        return $default;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and FALSE on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        if (empty($values)) {
            throw new InvalidArgumentException('Values is empty.');
        }
        if (!is_array($values)) {
            throw new InvalidArgumentException('Values is not a array.');
        }

        if (!$this->multi()) {
            return FALSE;
        }
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        if ($this->exec()) {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        if (empty($keys)) {
            throw new InvalidArgumentException('Keys is empty.');
        }
        if (!is_array($keys)) {
            throw new InvalidArgumentException('keys is not a array.');
        }

        $this->delete(...$keys);

        return TRUE;
    }

    /**
     * Returns TRUE if the container can return an entry for the given identifier.
     * Returns FALSE otherwise.
     *
     * `has($id)` returning TRUE does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $key Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->exists($key);
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
     *
     * @throws InvalidArgumentException
     *
     * @return mixed Can return all value types.
     *
     * @since 5.0.0
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
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
        $this->delete($offset);
    }
}
