<?php
/**
 * Http functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('url')) {
    /**
     * Get url
     *
     * @param string $path
     * @return string
     */
    function url(string $path = '')
    {
        $path = trim($path, ' /');
        $router = config_key('/' . $path, 'routerIndex');
        if (empty($router)) {
            throw new RuntimeException('Path "' . $path . '" not found in router config.');
        }

        return BASE_URL . $path;
    }
}
