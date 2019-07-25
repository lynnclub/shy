<?php

namespace Shy\Core\Libraries;

use redis as PhpRedis;
use shy\Http\exception\httpException;

/**
 * Redis数据库类
 */
class Redis
{
    /**
     * 实例数组
     *
     * @var array
     */
    protected static $instance = [];

    /**
     * 获取实例
     *
     * @param string $config_name
     * @return Redis
     */
    public function instance($config_name = 'default')
    {
        $config = config_key('redis', 'database');
        if (!isset($config[$config_name])) {
            throw new httpException(500, 'Redis Config ' . $config_name . ' not set');
        }
        if (!extension_loaded('redis')) {
            throw new httpException(500, 'Redis extension not find');
        }

        if (empty(self::$instance[$config_name])) {
            $config = $config[$config_name];
            self::$instance[$config_name] = new PhpRedis();
            self::$instance[$config_name]->pconnect($config['host'], $config['port']);
            if (isset($config['password'])) {
                self::$instance[$config_name]->auth($config['password']);
            }
            if (isset($config['database'])) {
                self::$instance[$config_name]->select($config['database']);
            }
            if (self::$instance[$config_name]->ping() !== '+PONG') {
                throw new httpException(500, 'Redis Config ' . $config_name . ': connect failed');
            }
        }
        return self::$instance[$config_name];
    }

    /**
     * 关闭数据库实例
     *
     * @param string $config_name
     */
    public function close($config_name = 'default')
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->close();
            self::$instance[$config_name] = null;
        }
    }

    /**
     * 关闭所有数据库实例
     */
    public function closeAll()
    {
        foreach (self::$instance as $connection) {
            $connection->close();
        }
        self::$instance = [];
    }
}
