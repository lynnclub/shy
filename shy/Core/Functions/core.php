<?php
/**
 * Core functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('shy')) {
    /**
     * Get or make instance
     *
     * @param string $id
     * @param object|string|null $concrete
     * @param array ...$parameters
     *
     * @return object
     */
    function shy($id = null, $concrete = null, ...$parameters)
    {
        if (is_null($id)) {
            return Shy\Core\Container::getContainer();
        }

        return Shy\Core\Container::getContainer()->getOrMake($id, $concrete, ...$parameters);
    }
}

if (!function_exists('bind')) {
    /**
     * Bind ready to make
     *
     * @param string $id
     * @param string|Closure|object|null $concrete
     *
     * @return Shy\Core\Container
     */
    function bind(string $id, $concrete = null)
    {
        return Shy\Core\Container::getContainer()->bind($id, $concrete);
    }
}

if (!function_exists('config_key')) {
    /**
     * Config key
     *
     * @param string $key
     * @param string $filename
     * @return mixed
     */
    function config_key(string $key, string $filename = 'app')
    {
        return shy(Shy\Core\Contracts\Config::class)->find($key, $filename);
    }
}

if (!function_exists('config')) {
    /**
     * Config
     *
     * @param string $filename
     * @return mixed
     */
    function config(string $filename = null)
    {
        if (is_null($filename)) {
            return shy(Shy\Core\Contracts\Config::class);
        }

        return shy(Shy\Core\Contracts\Config::class)->load($filename);
    }
}

if (!function_exists('require_file')) {
    /**
     * Require file get config
     *
     * @param string $filename
     *
     * @throws Exception
     *
     * @return mixed
     */
    function require_file(string $filename)
    {
        if (file_exists($filename)) {
            return require "$filename";
        } else {
            throw new Exception('require_file() file not exist ' . $filename);
        }
    }
}

if (!function_exists('is_cli')) {
    /**
     * Determine if running in console.
     *
     * @return bool
     */
    function is_cli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}
