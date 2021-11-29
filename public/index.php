<?php

/**
 * @var $container \Shy\Core\Container
 */
$container = require __DIR__ . '/../bootstrap/http.php';

// Hook
\Shy\Core\Facade\Hook::run('request_before');

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
$response = $container['router']->run($container['request']);
if (method_exists($response, 'output')) {
    $response->output();
} else {
    $container['response']->output($response);
}

// Hook
\Shy\Core\Facade\Hook::run('response_after');

// Clear Request
$container['request']->initialize();
