<?php

namespace shy\core\library;

use PDO as phpPdo;
use shy\http\exception\httpException;

/**
 * pdo封装类
 */
class pdo
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
        $config = config('mysql', 'database');
        if (!isset($config[$config_name])) {
            throw new httpException(500, 'Mysql Config ' . $config_name . ' not set');
        }
        if (!extension_loaded('pdo')) {
            throw new httpException(500, 'Pdo extension not find');
        }

        if (empty(self::$instance[$config_name])) {
            $config = $config[$config_name];
            self::$instance[$config_name] = new phpPdo(
                'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
                $config['username'],
                $config['password'],
                [phpPdo::ATTR_PERSISTENT => true]
            );
            self::$instance[$config_name]->setAttribute(phpPdo::ATTR_ERRMODE, phpPdo::ERRMODE_EXCEPTION);
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
            self::$instance[$config_name] = null;
        }
    }

    /**
     * 关闭所有数据库实例
     */
    public function closeAll()
    {
        foreach (self::$instance as $config_name => $connection) {
            self::$instance[$config_name] = null;
        }
        self::$instance = array();
    }
}
