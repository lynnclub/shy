<?php

/**
 * @var $container \Shy\Core\Container
 */
$container = require __DIR__ . '/../bootstrap/http.php';

$container['request']->initialize($_GET, $_POST, [], $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
$container['session']->sessionStart();

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
    });

// Clear Request
$container['request']->initialize();
