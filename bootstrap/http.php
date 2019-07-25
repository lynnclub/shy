<?php

/**
 * Shy Framework Http Bootstrap
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */


/**
 * --------------------------
 * Exception Handler Register
 * --------------------------
 */

try {
    $config = new Shy\Core\Config();
    $request = new Shy\Http\Request();
    $logger = new Shy\Core\Logger($request, $config);
    $response = new Shy\Http\Response();

    new Shy\Core\Exceptions\HandlerRegister(new Shy\Http\Exceptions\Handler(), $logger, $response, $config->find('debug'));
} catch (Throwable $throwable) {
    echo $throwable->getTraceAsString();
    exit(1);
}

/**
 * --------------------------
 * Start Http Services
 * --------------------------
 */

$http = new Shy\Http();

/**
 * Set Instances
 */
$http->sets([
    Shy\Core\Contracts\Config::class => $config,
    Shy\Core\Contracts\Logger::class => $logger,
    Shy\Http\Contracts\Request::class => $request,
    Shy\Http\Contracts\Response::class => $response
]);
unset($config, $logger, $request, $response);

/**
 * Core Services Aliases
 */
$http->aliases([
    'config' => Shy\Core\Contracts\Config::class,
    'pipeline' => Shy\Core\Contracts\Pipeline::class,
    'logger' => Shy\Core\Contracts\Logger::class,
    'session' => Shy\Http\Contracts\Session::class,
    'request' => Shy\Http\Contracts\Request::class,
    'response' => Shy\Http\Contracts\Response::class
]);

/**
 * Binding
 */
$http->binds([
    Shy\Core\Contracts\Pipeline::class => Shy\Core\Pipeline::class,
    Shy\Http\Contracts\Session::class => Shy\Http\Session::class,
    Shy\Http\Contracts\Router::class => Shy\Http\Router::class
]);

/**
 * Define constants
 */
defined('BASE_PATH') or define('BASE_PATH', $http['config']->find('base', 'path'));
defined('APP_PATH') or define('APP_PATH', $http['config']->find('app', 'path'));
defined('VIEW_PATH') or define('VIEW_PATH', $http['config']->find('view', 'path'));
defined('CACHE_PATH') or define('CACHE_PATH', $http['config']->find('cache', 'path'));

/**
 * Setting
 */
date_default_timezone_set($http['config']->find('timezone'));

if ($http['config']->find('illuminate_database')) {
    init_illuminate_database();
}

/**
 * Load helper functions
 */
require __DIR__ . '/../shy/Http/Functions/View.php';
