<?php

namespace Shy\Http\Session;

use BadFunctionCallException;
use Exception;
use Predis\Client as Predis;
use Shy\Cache\Redis as CacheRedis;
use SessionHandlerInterface;

class Redis implements SessionHandlerInterface
{
    /**
     * @var CacheRedis
     */
    protected $handler;

    protected $config;

    protected $sessionConfig;

    public function __construct($config = [])
    {
        $this->sessionConfig = $config;
        $this->config = config('cache.redis.' . $config['cache_redis']);
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
            $this->handler = new CacheRedis($this->config);
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach ($this->config as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication'])) {
                    $params[$key] = $val;
                    unset($this->config[$key]);
                }
            }
            $this->handler = new Predis($this->config, $params);
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
     */
    public function read($key)
    {
        $this->lock($key);

        $content = (string)$this->handler->get($this->sessionConfig['name'] . $key);

        $this->unlock($key);

        return $content;
    }

    /**
     * 写入
     *
     * @param string $id
     * @param mixed $data
     * @return bool
     */
    public function write($id, $data)
    {
        $this->lock($id);

        if ($this->sessionConfig['expire'] > 0) {
            $result = $this->handler->setex(
                $this->sessionConfig['name'] . $id,
                $this->sessionConfig['expire'],
                $data
            );
        } else {
            $result = $this->handler->set(
                $this->sessionConfig['name'] . $id,
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
        return $this->handler->del($this->sessionConfig['name'] . $id) > 0;
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

        $lockKey = $this->sessionConfig['name'] . 'LOCK_PREFIX_' . $id;
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

        $this->handler->del($this->sessionConfig['name'] . 'LOCK_PREFIX_' . $id);
    }
}
