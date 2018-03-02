<?php

namespace shy\lib;

use config\redis as DbConfig;
use Exception;
use redis as PhpRedis;

/**
 * Redis数据库类
 */
class redis
{
    /**
     * 实例数组
     *
     * @var array
     */
    protected static $instance = array();

    /**
     * 获取实例
     *
     * @param string $config_name
     * @return Redis
     * @throws Exception
     */
    public static function instance($config_name = 'default')
    {
        if (!isset(DbConfig::$$config_name)) {
            echo "\\Config\\Redis::$config_name not set\n";
            throw new Exception("\\Config\\Redis::$config_name not set\n");
        }

        if (empty(self::$instance[$config_name])) {
            $config = DbConfig::$$config_name;
            self::$instance[$config_name] = new PhpRedis();
            self::$instance[$config_name]->pconnect($config['host'], $config['port']);
            if (isset($config['auth'])) {
                self::$instance[$config_name]->auth($config['auth']);
            }
            if (isset($config['select'])) {
                self::$instance[$config_name]->select($config['select']);
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
            $connection->closeConnection();
        }
        self::$instance = array();
    }
}
