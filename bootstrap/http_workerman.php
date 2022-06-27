<?php

use Shy\Container;
use Shy\Exception\HandlerRegister;

// 契约 Contract
use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\Contract\Response as ResponseContract;
use Shy\Http\Contract\Session as SessionContract;
use Shy\Http\Contract\Router as RouterContract;
use Shy\Http\Contract\View as ViewContract;
use Shy\Contract\Logger as LoggerContract;

// 组件 Component
use Shy\Http\Request;
use Shy\Http\Router;
use Shy\Http\View;
use Shy\Socket\WorkerMan\Session;
use Shy\Socket\WorkerMan\Response;

try {
    // 容器已经在命令模式下启动，仅补充http相关参数
    // The container has been started in command mode, only http related parameters are added.
    $container = Container::getContainer();
    $container->binds([
        ResponseContract::class => Response::class,
        SessionContract::class => Session::class,
        RouterContract::class => Router::class,
        ViewContract::class => View::class,
    ])->aliases([
        'session' => SessionContract::class,
        'router' => RouterContract::class,
        'request' => RequestContract::class,
        'response' => ResponseContract::class,
        'view' => ViewContract::class,
    ]);

    // 会话初始化
    $container['session']->init(config('session'));

    // 装载请求，并加入到容器 Load the request and join to the container
    $container->set(RequestContract::class, Request::createFromGlobals());

    // 定义基础地址 Define BASE_URL
    if (!defined('BASE_URL')) {
        if (empty($base_url = config('app.base_url'))) {
            define('BASE_URL', $container['request']->getUriForPath('/'));
        } else {
            define('BASE_URL', rtrim($base_url, '/') . '/');
        }
    }

    // 补充http模式缺失的组件 Supplementary components missing from http mode
    $container[LoggerContract::class]->setRequest($container->get(RequestContract::class));
    $container[HandlerRegister::class]->setView($container->make(ViewContract::class))
        ->setResponse($container->make(ResponseContract::class));

    // 设置http复用循环初始值 Set the initial value of the http multiplexing loop
    $container->set('HTTP_LOOP_COUNT', 0);

    // 启动实例智能调度
    //$container->enableIntelligentScheduling();

    // 加载文件 Loading files
    require __DIR__ . '/../shy/Http/Function/view.php';

    return $container;
} catch (Throwable $throwable) {
    echo implode(PHP_EOL, get_throwable_array($throwable));
    exit(1);
}
