<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contracts\Router as RouterContracts;

/**
 * Class Router
 * @package Shy\Http\Facades
 *
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
