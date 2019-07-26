<?php

/**
 * Shy Framework Http In CLI Bootstrap
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

/**
 * Bind Dependencies
 */
$container->binds([
    Shy\Core\Contracts\Logger::class => Shy\Core\Logger::class,
    Shy\Core\Contracts\ExceptionHandler::class => Shy\Http\Exceptions\Handler::class,
    Shy\Http\Contracts\Request::class => Shy\Http\Request::class,
    Shy\Http\Contracts\Response::class => Shy\Http\Response::class,
    Shy\Http\Contracts\Session::class => Shy\Socket\WorkerMan\Session::class,
    Shy\Http\Contracts\Router::class => Shy\Http\Router::class
]);

/**
 * Core Services Aliases
 */
$container->aliases([
    'http' => Shy\Http::class,
    'session' => Shy\Http\Contracts\Session::class,
    'request' => Shy\Http\Contracts\Request::class,
    'response' => Shy\Http\Contracts\Response::class
]);

/**
 * Renew Core Services
 */
$container->make(Shy\Core\Contracts\Logger::class);
$container->make(Shy\Core\Exceptions\HandlerRegister::class);

/*
 * --------------------------
 * Make Http Service
 * --------------------------
 */

/**
 * Load helper functions
 */
require __DIR__ . '/../shy/Http/Functions/View.php';

$container->make(Shy\Http::class);
