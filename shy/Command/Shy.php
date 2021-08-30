<?php

namespace Shy\Command;

class Shy
{
    /**
     * The List of Command
     *
     * @return string
     */
    public function commandList()
    {
        $list = array_keys(config('command'));
        asort($list);

        return implode(PHP_EOL, $list);
    }

    /**
     * SHY_ENV
     *
     * @return mixed
     */
    public function env()
    {
        return defined('SHY_ENV') ? SHY_ENV : 'Not defined';
    }

    /**
     * Route config
     *
     * @return string
     */
    public function routeConfig()
    {
        bind(\Shy\Http\Contracts\Request::class, \Shy\Http\Request::class);

        $router = shy(\Shy\Http\Contracts\Router::class, \Shy\Http\Router::class);
        $router->initialize();
        $router->buildRouteByConfig();

        return json_encode($router->getRouteConfig());
    }

    /**
     * Route index
     *
     * @return string
     */
    public function routeIndex()
    {
        bind(\Shy\Http\Contracts\Request::class, \Shy\Http\Request::class);

        $router = shy(\Shy\Http\Contracts\Router::class, \Shy\Http\Router::class);
        $router->initialize();
        $router->buildRouteByConfig();

        return json_encode($router->getRouteIndex());
    }
}
