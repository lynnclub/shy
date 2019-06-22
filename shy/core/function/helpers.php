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
     * @return \shy\core\container
     */
    function container()
    {
        static $_SHY_CONTAINER;
        if (!$_SHY_CONTAINER instanceof shy\core\container) {
            $_SHY_CONTAINER = new shy\core\container();
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

if (!function_exists('init_illuminate_database')) {
    /**
     * Init illuminate database
     *
     * @return object Illuminate\Database\Capsule\Manager
     */
    function init_illuminate_database()
    {
        $capsule = shy('capsule', 'Illuminate\Database\Capsule\Manager');
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

if (!function_exists('logger')) {
    /**
     * Log
     *
     * @param string $msg
     * @param string $level
     * @param string $filename
     * @param string $datetimeFormat
     */
    function logger(string $msg, string $level = 'INFO', string $filename = '', string $datetimeFormat = 'Y-m-d')
    {
        if (empty($filename)) {
            if (config('IS_CLI')) {
                $filename = 'console/';
            } else {
                $filename = 'web/';
            }
        }
        if ($datetimeFormat) {
            $filename .= date($datetimeFormat);
        }
        $filename = config_key('cache', 'path') . 'log/' . $filename . '.log';
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename));
        }

        $prefix = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ';
        $request = shy('request', 'shy\http\request');
        if (is_object($request)) {
            $ips = $request->getClientIps();
            if (!empty($ips)) {
                $prefix .= '[' . implode(',', $ips);
            }

            $url = $request->getUrl();
            if (!empty($url)) {
                $prefix .= ' ' . $url . '] ';
            }
        }
        @file_put_contents($filename, $prefix . $msg . PHP_EOL, FILE_APPEND);
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
