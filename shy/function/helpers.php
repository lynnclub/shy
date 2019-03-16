<?php

/**
 * Functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('config')) {
    /**
     * Get Config
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
     * Get Config File
     *
     * @param string $file
     * @return bool|array
     */
    function config_all($file = 'app')
    {
        static $config = [];
        static $config_path = __DIR__ . '/../../config/';
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
     * Require Config
     *
     * @param $filename
     * @return bool|mixed
     */
    function require_config($filename)
    {
        $filename .= '.php';
        if (file_exists($filename)) {

            return require_once "$filename";
        }

        return false;
    }
}

if (!function_exists('shy')) {
    /**
     * Get Or Make Object
     *
     * @param $abstract
     * @return object
     */
    function shy($abstract)
    {
        return make($abstract);
    }
}

if (!function_exists('bind')) {
    /**
     * bind object
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

if (!function_exists('make')) {
    /**
     * Get Or Make Object
     *
     * @param $abstract
     * @param object|Closure|string $concrete
     * @param array ...$parameters
     * @return object
     * @throws
     */
    function make($abstract, $concrete = null, ...$parameters)
    {
        global $_container;
        return $_container->make($abstract, $concrete, ...$parameters);
    }
}

if (!function_exists('view')) {
    /**
     * make view
     *
     * @param $view
     * @param array $params
     * @param string $layout
     * @return mixed
     */
    function view($view, $params = [], $layout = '')
    {
        $view = shy('view')->view($view);
        if (isset($params)) {
            $view->with($params);
        }
        if (isset($layout)) {
            $view->layout($layout);
        }
        return $view;
    }
}

if (!function_exists('include_view')) {
    /**
     * include View
     *
     * @param $filename
     * @param array $params
     * @return bool|string
     */
    function include_view($filename, $params = [])
    {
        $filename .= '.php';
        if (file_exists($filename)) {
            if (!empty($params)) {
                extract($params);
            }

            ob_start();
            require_once "$filename";
            $_content = ob_get_contents();
            ob_end_clean();

            return $_content;
        }

        return false;
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
     * development debug
     *
     * @param mixed $msg
     */
    function dd(...$msg)
    {
        foreach ($msg as $item) {
            var_dump($item);
        }

        exit;
    }
}
