<?php

use Shy\Container;
use Shy\Facade\Hook;
use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\Request;

/**
 * 执行启动器，得到容器
 *
 * @var $container Container
 */
$container = require __DIR__ . '/../bootstrap/http.php';

// 装载请求，并加入到容器 Load the request and join to the container
$container->set(RequestContract::class, Request::createFromGlobals());

// 启动会话
$container['session']->sessionStart();

// 定义基础地址 Define BASE_URL
if (!defined('BASE_URL')) {
    if (empty($base_url = config('app.base_url'))) {
        define('BASE_URL', $container['request']->getUriForPath('/'));
    } else {
        define('BASE_URL', rtrim($base_url, '/') . '/');
    }
}

// 钩子-请求处理前
Hook::run('request_before');

// 处理请求，输出响应 Process the request and output the response
$response = $container['router']->run($container['request']);
if (method_exists($response, 'output')) {
    $response->output();
} else {
    $container['response']->output($response);
}

// 钩子-响应后
Hook::run('response_after');
