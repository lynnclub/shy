<?php

/**
 * Shy Framework Http In WorkerMan Bootstrap
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */


/*
 * --------------------------
 * Load Http Services
 * --------------------------
 */

$container = Shy\Core\Container::getContainer();

$container->instancesIntelligentSchedulingOn(__DIR__ . '/../cache/instances_record/');
$container->set('SHY_CYCLE_COUNT', 0);

/**
 * Bind Dependencies
 */
$container->binds([
    Shy\Http\Contracts\Request::class => Shy\Http\Request::class,
    Shy\Http\Contracts\Response::class => Shy\Http\Response::class,
    Shy\Http\Contracts\Session::class => Shy\Socket\WorkerMan\Session::class,
    Shy\Http\Contracts\Router::class => Shy\Http\Router::class,
    Shy\Http\Contracts\View::class => Shy\Http\View::class
]);

/**
 * Core Services Aliases
 */
$container->aliases([
    'http' => Shy\Http::class,
    'session' => Shy\Http\Contracts\Session::class,
    'request' => Shy\Http\Contracts\Request::class,
    'response' => Shy\Http\Contracts\Response::class,
    'view' => Shy\Http\Contracts\View::class
]);

/**
 * Update Dependencies
 */
$container['logger']->setRequest($container->make(Shy\Http\Contracts\Request::class));
$container[Shy\Core\Exceptions\HandlerRegister::class]->setResponse($container->make(Shy\Http\Contracts\Response::class));

/*
 * --------------------------
 * Make Http Service
 * --------------------------
 */

/**
 * Load helper functions
 */
require __DIR__ . '/../shy/Http/Functions/view.php';

$container->make(Shy\Http::class);
