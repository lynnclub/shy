<?php
/**
 * Core functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('container')) {
    /**
     * Container
     *
     * @param string $configDir
     * @return \shy\core\container
     */
    function container(string $configDir = '')
    {
        static $_SHY_CONTAINER;
        if (!$_SHY_CONTAINER instanceof shy\core\container) {
            $_SHY_CONTAINER = new shy\core\container($configDir);
        }

        return $_SHY_CONTAINER;
    }
}

if (!function_exists('config_set')) {
    /**
     * Set config
     *
     * @param string $abstract
     * @param $config
     * @return mixed
     */
    function config_set(string $abstract, $config)
    {
        return container()->setConfig($abstract, $config);
    }
}

if (!function_exists('config_exist')) {
    /**
     * Is config exist
     *
     * @param string $abstract
     * @return bool
     */
    function config_exist(string $abstract)
    {
        return container()->configExist($abstract);
    }
}

if (!function_exists('config_del')) {
    /**
     * Remove config
     *
     * @param string $abstract
     */
    function config_del(string $abstract)
    {
        container()->removeConfig($abstract);
    }
}

if (!function_exists('config')) {
    /**
     * Get config
     *
     * @param string $abstract
     * @param string $default
     * @return mixed
     */
    function config(string $abstract = 'app', $default = '')
    {
        return container()->getConfig($abstract, $default);
    }
}

if (!function_exists('config_key')) {
    /**
     * Get config array value by key
     *
     * @param string $key
     * @param string $abstract
     * @return mixed
     */
    function config_key(string $key, string $abstract = 'app')
    {
        $config = config($abstract);
        if (isset($config[$key])) {
            return $config[$key];
        }
    }
}

if (!function_exists('config_all')) {
    /**
     * Get all config
     *
     * @return mixed
     */
    function config_all()
    {
        return container()->getAllConfig();
    }
}

if (!function_exists('config_int_calc')) {
    /**
     * Config int
     *
     * @param string $abstract
     * @param int $int
     * @return int
     */
    function config_int_calc(string $abstract, int $int = 1)
    {
        return container()->configIntCalc($abstract, $int);
    }
}

if (!function_exists('config_array_push')) {
    /**
     * Push config
     *
     * @param string $abstract
     * @param string|array $config
     * @return array
     */
    function config_array_push(string $abstract, $config)
    {
        $oldConfig = config($abstract);
        if (empty($oldConfig)) {
            $oldConfig = [];
        }
        if (!is_array($oldConfig)) {
            throw new RuntimeException('config_array_push() config ' . $abstract . ' must be array');
        }

        if (!empty($config)) {
            array_push($oldConfig, $config);
            $oldConfig = array_unique($oldConfig);
            config_del($abstract);
            config_set($abstract, $oldConfig);
        }

        return $oldConfig;
    }
}

if (!function_exists('require_file')) {
    /**
     * Require file get config
     *
     * @param string $filename
     * @return mixed
     */
    function require_file(string $filename)
    {
        if (file_exists($filename)) {
            return require "$filename";
        } else {
            throw new RuntimeException('require_file() file not exist ' . $filename);
        }
    }
}

if (!function_exists('bind')) {
    /**
     * Bind instance or closure ready to join container
     *
     * @param string $abstract
     * @param object|Closure $concrete
     * @return object container
     */
    function bind(string $abstract, $concrete)
    {
        return container()->bind($abstract, $concrete);
    }
}

if (!function_exists('make_new')) {
    /**
     * Make new instance and join container
     *
     * @param string $abstract
     * @param object|Closure|string $concrete
     * @param array ...$parameters
     * @return object
     */
    function make_new(string $abstract, $concrete = null, ...$parameters)
    {
        $make = function ($abstract, $concrete = null, ...$parameters) {
            return container()->makeNew($abstract, $concrete, ...$parameters);
        };

        return $make($abstract, $concrete, ...$parameters);
    }
}

if (!function_exists('shy')) {
    /**
     * Get instance or make new
     *
     * @param string $abstract
     * @param object|Closure|string $concrete
     * @param array ...$parameters
     * @return object
     */
    function shy(string $abstract, $concrete = null, ...$parameters)
    {
        $make = function ($abstract, $concrete = null, ...$parameters) {
            return container()->getOrMakeNew($abstract, $concrete, ...$parameters);
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
        return container()->getList();
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
        return container()->getListMemoryUsed();
    }
}

if (!function_exists('shy_clear')) {
    /**
     * Clear instance
     *
     * @param string|array $abstract
     */
    function shy_clear($abstract)
    {
        container()->clear($abstract);
    }
}

if (!function_exists('shy_clear_all')) {
    /**
     * Clear all instances
     */
    function shy_clear_all()
    {
        container()->clearAll();
    }
}

if (!function_exists('in_shy_list')) {
    /**
     * Is in instances list
     *
     * @param string $abstract
     * @return bool
     */
    function in_shy_list(string $abstract)
    {
        return container()->inList($abstract);
    }
}
