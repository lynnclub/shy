<?php

/**
 * 执行启动器，返回容器
 *
 * @var $container \Shy\Container
 */
$container = require __DIR__ . '/../bootstrap/http.php';

// 钩子
\Shy\Facade\Hook::run('request_before');

// 组装请求
$container['request']::createFromGlobals();
dd($container['request']->getSchemeAndHttpHost() . $container['request']->getBaseUrl(), $container['request']->getUriForPath($container['request']->getPathInfo()));

// 启动会话
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
\Shy\Facade\Hook::run('response_after');

// Clear Request
$container['request']->initialize();
