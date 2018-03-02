<?php

namespace shy\lib;

use config\db as DbConfig;
use Exception;
use mysqli;

/**
 * Redis数据库类
 */
class db
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
            echo "\\Config\\Db::$config_name not set\n";
            throw new Exception("\\Config\\Db::$config_name not set\n");
        }

        if (empty(self::$instance[$config_name])) {
            $config = DbConfig::$$config_name;
            self::$instance[$config_name] = new mysqli();
            self::$instance[$config_name]->connect($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        }
        return self::$instance[$config_name];
    }

    /**
     * 关闭数据库实例
     *
     * @param string $config_name
     */
    public static function close($config_name = 'default')
    {
        if (isset(self::$instance[$config_name])) {
            self::$instance[$config_name]->close();
            self::$instance[$config_name] = null;
        }
    }

    /**
     * 关闭所有数据库实例
     */
    public static function closeAll()
    {
        foreach (self::$instance as $connection) {
            $connection->closeConnection();
        }
        self::$instance = array();
    }
}
