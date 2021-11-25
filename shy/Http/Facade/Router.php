<?php

namespace Shy\Http\Facade;

use Shy\Core\Facade;
use Shy\Http\Contract\Router as RouterContracts;

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
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(RouterContracts::class);
    }
}
