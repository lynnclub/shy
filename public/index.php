<?php

/**
 * @var $container \Shy\Core\Container
 */
$container = require __DIR__ . '/../bootstrap/http.php';

// Hook
\Shy\Core\Facades\Hook::run('request_before');

$container['request']->initialize($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
$container['session']->sessionStart();

if (!defined('BASE_URL')) {
    if (empty($base_url = config('app.base_url'))) {
        define('BASE_URL', $container['request']->getSchemeAndHttpHost() . $container['request']->getBaseUrl() . '/');
    } else {
        define('BASE_URL', rtrim($base_url, '/') . '/');
    }

    unset($base_url);
}

// Run
$container->make(\Shy\Core\Contracts\Pipeline::class)
    ->send($container['request'])
    ->through(\Shy\Http\Contracts\Router::class)
    ->then(function ($body) use ($container) {
        if ($body instanceof \Shy\Http\Contracts\Response) {
            $body->output();
        } else {
            $container['response']->output($body);
        }

        // Hook
        \Shy\Core\Facades\Hook::run('response_after');
    });

// Clear Request
$container['request']->initialize();
