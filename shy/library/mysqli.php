<?php

namespace shy\library;

use mysqli as phpMysqli;
use shy\http\exception\httpException;

/**
 * mysqli封装类
 */
class mysqli
{
    private function __construct()
    {
        // not allow new outside
    }

    private function __clone()
    {
        // not allow clone outside
    }

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
     * @return mixed
     */
    public static function instance($config_name = 'default')
    {
        $config = config('mysql', 'database');
        if (!isset($config[$config_name])) {
            throw new httpException(500, 'Mysql Config ' . $config_name . ' not set');
        }
        if (!extension_loaded('mysqli')) {
            throw new httpException(500, 'Mysqli extension not find.');
        }

        if (empty(self::$instance[$config_name])) {
            $config = $config[$config_name];
            self::$instance[$config_name] = new phpMysqli();
            self::$instance[$config_name]->connect(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );
            if (self::$instance[$config_name]->connect_errno) {
                throw new httpException(500, 'Mysql Config ' . $config_name . ': connect failed.');
            }
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
            $connection->close();
        }
        self::$instance = [];
    }
}
