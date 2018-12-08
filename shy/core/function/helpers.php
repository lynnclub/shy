<?php

/**
 * Core functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('config')) {
    /**
     * Get config
     *
     * @param $key
     * @param string $file
     * @return bool
     */
    function config($key, $file = 'app')
    {
        $config = config_all($file);
        if (isset($config[$key])) {
            return $config[$key];
        } else {
            return false;
        }
    }
}

if (!function_exists('config_all')) {
    /**
     * Get all config of the config file
     *
     * @param string $file
     * @return bool|array
     */
    function config_all($file = 'app')
    {
        static $config = [];
        static $config_path = __DIR__ . '/../../../config/';
        if (isset($config[$file])) {
            return $config[$file];
        } else {
            $config[$file] = require_config($config_path . $file);
            return $config[$file];
        }
    }
}

if (!function_exists('require_config')) {
    /**
     * Require file get config
     *
     * @param $filename
     * @return bool|mixed
     */
    function require_config($filename)
    {
        $filename .= '.php';
        if (file_exists($filename)) {
            return require "$filename";
        }

        return false;
    }
}

if (!function_exists('bind')) {
    /**
     * Bind instance or closure ready to join container
     *
     * @param string $abstract
     * @param object $concrete
     * @return object container
     */
    function bind($abstract, $concrete)
    {
        global $_container;
        return $_container->bind($abstract, $concrete);
    }
}

if (!function_exists('make_new')) {
    /**
     * Make new instance and join container
     *
     * @param $abstract
     * @param object|Closure|string $concrete
     * @param array ...$parameters
     * @return object
     */
    function make_new($abstract, $concrete = null, ...$parameters)
    {
        $make = function ($abstract, $concrete = null, ...$parameters) {
            global $_container;
            return $_container->makeNew($abstract, $concrete, ...$parameters);
        };

        return $make($abstract, $concrete, ...$parameters);
    }
}

if (!function_exists('shy')) {
    /**
     * Get instance or make new
     *
     * @param $abstract
     * @param object|Closure|string $concrete
     * @param array ...$parameters
     * @return object
     */
    function shy($abstract, $concrete = null, ...$parameters)
    {
        $make = function ($abstract, $concrete = null, ...$parameters) {
            global $_container;
            return $_container->getOrMakeNew($abstract, $concrete, ...$parameters);
        };

        return $make($abstract, $concrete, ...$parameters);
    }
}

if (!function_exists('shy_list')) {
    /**
     * Get instances list
     *
     * @return array
     */
    function shy_list()
    {
        global $_container;
        return $_container->getList();
    }
}

if (!function_exists('shy_list_memory_used')) {
    /**
     * Get instances list of memory used
     *
     * @return array
     */
    function shy_list_memory_used()
    {
        global $_container;
        return $_container->getListMemoryUsed();
    }
}

if (!function_exists('shy_clear')) {
    /**
     * Clear instance
     *
     * @param string $abstract
     */
    function shy_clear($abstract)
    {
        global $_container;
        $_container->clear($abstract);
    }
}

if (!function_exists('shy_clear_all')) {
    /**
     * Clear all instances
     */
    function shy_clear_all()
    {
        global $_container;
        $_container->clearAll();
    }
}

if (!function_exists('in_shy_list')) {
    /**
     * Is in instances list
     *
     * @param string $abstract
     * @return bool
     */
    function in_shy_list($abstract)
    {
        global $_container;
        return $_container->inList($abstract);
    }
}

if (!function_exists('logger')) {
    /**
     * Log
     *
     * @param string $filename
     * @param string $msg
     * @param string $datetimeFormat
     */
    function logger($filename, $msg, $datetimeFormat = 'Y-m-d')
    {
        if ($datetimeFormat) {
            $filename .= date($datetimeFormat);
        }
        $filename = config('cache', 'path') . 'log/' . $filename . '.log';
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename));
        }
        @file_put_contents($filename, '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL, FILE_APPEND);
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
