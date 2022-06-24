<?php

namespace Shy\Http\Session;

use BadFunctionCallException;
use Exception;
use Predis\Client as Predis;
use SessionHandlerInterface;
use Shy\Cache\Redis as CacheRedis;
use Shy\Exception\Cache\InvalidArgumentException;

class Redis implements SessionHandlerInterface
{
    /**
     * @var CacheRedis
     */
    protected $handler;

    protected $config;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    /**
     * 初始化
     *
     * @param $path
     * @param $name
     * @return bool
     * @throws Exception
     */
    public function open($path, $name)
    {
        if (extension_loaded('redis')) {
            $this->handler = new CacheRedis($this->config['cache_redis']);
        } elseif (class_exists('\Predis\Client')) {
            $config = config('cache.redis.' . $this->config['cache_redis']);

            $params = [];
            foreach ($config as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication'])) {
                    $params[$key] = $val;
                    unset($config[$key]);
                }
            }
            $this->handler = new Predis($config, $params);
        } else {
            throw new BadFunctionCallException('not support: redis');
        }

        return true;
    }

    /**
     * 关闭Session
     */
    public function close()
    {
        $this->handler->close();
        $this->handler = null;

        return true;
    }

    /**
     * 读取
     *
     * @param string $key
     * @return string
     * @throws InvalidArgumentException
     */
    public function read($key)
    {
        $this->lock($key);

        $content = (string)$this->handler->get($this->config['name'] . ':' . $key);

        $this->unlock($key);

        return $content;
    }

    /**
     * 写入
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     * @throws Exception
     */
    public function write($id, $data)
    {
        $this->lock($id);

        if ($this->config['expire'] > 0) {
            $result = $this->handler->setex(
                $this->config['name'] . ':' . $id,
                $this->config['expire'],
                $data
            );
        } else {
            $result = $this->handler->set(
                $this->config['name'] . ':' . $id,
                $data
            );
        }

        $this->unlock($id);

        return (bool)$result;
    }

    /**
     * 删除
     *
     * @param string $id
     * @return bool
     */
    public function destroy($id)
    {
        return $this->handler->del($this->config['name'] . ':' . $id) > 0;
    }

    /**
     * 垃圾回收
     *
     * @param $max_lifetime
     * @return bool
     */
    public function gc($max_lifetime)
    {
        return true;
    }

    /**
     * 读写加锁
     *
     * @param string $id
     * @param int $lockTimeout
     * @return void
     * @throws Exception
     */
    protected function lock(string $id, $lockTimeout = 3)
    {
        $t = time();

        do {
            if (time() - $t > $lockTimeout) {
                $this->unlock($id);
            }
        } while (!$this->doLock($id, $lockTimeout));
    }

    /**
     * 加锁
     *
     * @param string $id
     * @param int $timeout 过期时间
     * @return bool
     * @throws Exception
     */
    public function doLock(string $id, $timeout = 10)
    {
        if (null == $this->handler) {
            $this->open('', '');
        }

        $lockKey = $this->config['name'] . ':lock:' . $id;
        $isLock = $this->handler->setnx($lockKey, 1);
        if ($isLock) {
            // 设置过期时间，防止死锁
            $this->handler->expire($lockKey, $timeout);
            return true;
        }

        return false;
    }

    /**
     * 解锁
     *
     * @param string $id
     * @throws Exception
     */
    public function unlock(string $id)
    {
        if (null == $this->handler) {
            $this->open('', '');
        }

        $this->handler->del($this->config['name'] . ':lock:' . $id);
    }
}
