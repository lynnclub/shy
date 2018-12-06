<?php

/**
 * Functions
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

if (!function_exists('makeNew')) {
    /**
     * Make new instance and join container
     *
     * @param $abstract
     * @param object|Closure|string $concrete
     * @param array ...$parameters
     * @return object
     * @throws RuntimeException|ReflectionException
     */
    function makeNew($abstract, $concrete = null, ...$parameters)
    {
        global $_container;
        return $_container->makeNew($abstract, $concrete, ...$parameters);
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

if (!function_exists('view')) {
    /**
     * New view
     *
     * @param $view
     * @param array $params
     * @param string $layout
     * @return mixed
     * @throws ReflectionException
     */
    function view($view, $params = [], $layout = '')
    {
        $view = makeNew('view', 'shy\http\view')->view($view);
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
     * Include View
     *
     * @param $filename
     * @param array $params
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
            ob_end_flush();
        } else {
            shy('view')->error('[view] Include view ' . $filename . ' is not exist.');
        }
    }
}

if (!function_exists('include_sub_view')) {
    /**
     * Include sub view
     */
    function include_sub_view()
    {
        $subViewContent = shy('view')->getSubView();
        if (empty($subViewContent) || !is_string($subViewContent)) {
            shy('view')->error('[view] Include sub view failed.');
        } else {
            echo $subViewContent;
        }
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

if (!function_exists('param')) {
    /**
     * output param
     *
     * @param $key
     */
    function param($key)
    {
        $params = shy('view')->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            echo $params[$key];
        } else {
            shy('view')->error('[view] Param ' . $key . ' is not exist.');
        }
    }
}
