<?php

/**
 * Shy Framework Http Bootstrap
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */


/*
 * --------------------------
 * Register Exception Handler
 * --------------------------
 */

try {
    $container = Shy\Core\Container::getContainer();

    /**
     * Bind Dependencies
     */
    $container->binds([
        Shy\Core\Contracts\Config::class => Shy\Core\Config::class,
        Shy\Core\Contracts\Logger::class => Shy\Core\Logger::class,
        Shy\Core\Contracts\ExceptionHandler::class => Shy\Http\Exceptions\Handler::class,
        Shy\Core\Contracts\Pipeline::class => Shy\Core\Pipeline::class,
        Shy\Core\Contracts\Cache::class => Shy\Core\Cache\RedisCache::class,
        Shy\Core\Contracts\DataBase::class => Shy\Core\DataBase\Pdo::class,
        Shy\Http\Contracts\Request::class => Shy\Http\Request::class,
        Shy\Http\Contracts\Response::class => Shy\Http\Response::class,
        Shy\Http\Contracts\Session::class => Shy\Http\Session::class,
        Shy\Http\Contracts\Router::class => Shy\Http\Router::class,
        Shy\Http\Contracts\View::class => Shy\Http\View::class
    ]);

    /**
     * Core Services Aliases
     */
    $container->aliases([
        'config' => Shy\Core\Contracts\Config::class,
        'pipeline' => Shy\Core\Contracts\Pipeline::class,
        'logger' => Shy\Core\Contracts\Logger::class,
        'http' => Shy\Http::class,
        'session' => Shy\Http\Contracts\Session::class,
        'request' => Shy\Http\Contracts\Request::class,
        'response' => Shy\Http\Contracts\Response::class,
        'view' => Shy\Http\Contracts\View::class
    ]);

    /**
     * Register Handler
     */
    $container->make(Shy\Core\Exceptions\HandlerRegister::class);

} catch (Throwable $throwable) {
    echo nl2br($throwable->getMessage() . PHP_EOL . $throwable->getTraceAsString());
    exit(1);
}

/*
 * --------------------------
 * Make Http Service
 * --------------------------
 */

/**
 * Define Constants
 */
defined('BASE_PATH') or define('BASE_PATH', $container['config']->find('base', 'path'));
defined('APP_PATH') or define('APP_PATH', $container['config']->find('app', 'path'));
defined('VIEW_PATH') or define('VIEW_PATH', $container['config']->find('view', 'path'));
defined('CACHE_PATH') or define('CACHE_PATH', $container['config']->find('cache', 'path'));

/**
 * Setting
 */
date_default_timezone_set($container['config']->find('timezone'));

/**
 * Load helper functions
 */
require __DIR__ . '/../shy/Http/Functions/View.php';

$container->make(Shy\Http::class);
