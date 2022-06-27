<?php

namespace Shy\Command;

use Shy\Http\Contract\Request;
use Shy\Http\Contract\Router;

class Shy
{
    /**
     * 命令列表
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
     * 环境变量
     * SHY_ENV
     *
     * @return string
     */
    public function env()
    {
        return defined('SHY_ENV') ? SHY_ENV : 'Not defined';
    }

    /**
     * 路由配置
     * Route config
     *
     * @return string
     */
    public function routeConfig()
    {
        bind(Request::class, \Shy\Http\Request::class);

        $router = shy(Router::class, \Shy\Http\Router::class);
        $router->initialize();
        $router->buildRoute();

        return json_encode($router->getRouteConfig());
    }

    /**
     * 路由索引
     * Route index
     *
     * @return string
     */
    public function routeIndex()
    {
        $router = shy(Router::class, \Shy\Http\Router::class);
        $router->buildRoute();

        return json_encode($router->getRouteIndex());
    }
}
