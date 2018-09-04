<?php

/**
 * Functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

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
     * Make And Get Object
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

if (!function_exists('shy')) {
    /**
     * Get or Make Object
     *
     * @param $abstract
     * @return object
     * @throws
     */
    function shy($abstract)
    {
        global $_container;
        return $_container->make($abstract);
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

if (!function_exists('require_config')) {
    function require_config($filename)
    {
        $filename .= '.php';
        if (file_exists($filename)) {

            return require_once "$filename";
        }

        return false;
    }
}

if (!function_exists('config')) {
    function config($key, $file = 'app')
    {
        return shy('config')->get($key, $file);
    }
}

if (!function_exists('config_all')) {
    function config_all($file = 'app')
    {
        return shy('config')->getAll($file);
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
        @file_put_contents($filename, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\r\n", FILE_APPEND);
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
