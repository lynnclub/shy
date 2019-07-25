<?php
/**
 * Helpers functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('init_illuminate_database')) {
    /**
     * Init illuminate database
     *
     * @return bool|object Illuminate\Database\Capsule\Manager
     */
    function init_illuminate_database()
    {
        if (!class_exists('Illuminate\Database\Capsule\Manager')) {
            return false;
        }

        $capsule = shy('Illuminate\Database\Capsule\Manager');
        $database = config_key('db', 'database');
        if (is_array($database)) {
            $capsule->setAsGlobal();
            foreach ($database as $name => $item) {
                if (isset($item['driver'], $item['host'], $item['port'], $item['database'], $item['username'], $item['password'], $item['charset'], $item['collation'])) {
                    $capsule->addConnection([
                        'driver' => $item['driver'],
                        'host' => $item['host'],
                        'database' => $item['database'],
                        'username' => $item['username'],
                        'password' => $item['password'],
                        'charset' => $item['charset'],
                        'collation' => $item['collation'],
                        'prefix' => '',
                    ], $name);
                } else {
                    throw new RuntimeException('Database config error.');
                }
            }
            return $capsule;
        } else {
            throw new RuntimeException('Database config error.');
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Development output
     *
     * @param mixed $msg
     */
    function dd(...$msg)
    {
        foreach ($msg as $item) {
            var_dump($item);
        }

        exit(0);
    }
}
