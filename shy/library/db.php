<?php

namespace shy\library;

use mysqli;
use Exception;

/**
 * mysqli封装类
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
     */
    public static function instance($config_name = 'default')
    {
        $config = config('mysql', 'database');
        if (!isset($config[$config_name])) {
            showError(500, 'Mysql Config ' . $config_name . ' not set');
        }
        if (!extension_loaded('mysqli')) {
            showError(500, 'mysqli extension not find');
        }

        if (empty(self::$instance[$config_name])) {
            try {
                $config = $config[$config_name];
                self::$instance[$config_name] = new mysqli();
                self::$instance[$config_name]->connect(
                    $config['host'],
                    $config['username'],
                    $config['password'],
                    $config['database'],
                    $config['port']
                );
                if (self::$instance[$config_name]->connect_errno) {
                    throw new Exception('Mysql Config ' . $config_name . ': connect failed');
                }
            } catch (Exception $e) {
                showError(500, $e->getMessage());
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
