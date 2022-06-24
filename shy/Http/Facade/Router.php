<?php

namespace Shy\Http\Facade;

use Shy\Facade;
use Shy\Http\Contract\Router as RouterContract;

/**
 * Class Router
 * @package Shy\Http\Facade
 *
 * @method static string getPathInfo()
 * @method static string getController()
 * @method static string getMethod()
 * @method static array getMiddleware()
 */
class Router extends Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(RouterContract::class);
    }
}
