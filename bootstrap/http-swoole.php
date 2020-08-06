<?php

use Shy\Core\Container;
use Shy\Core\Exceptions\HandlerRegister;

use Shy\Http\Contracts\Request as RequestContract;
use Shy\Http\Contracts\Response as ResponseContract;
use Shy\Http\Contracts\Session as SessionContract;
use Shy\Http\Contracts\Router as RouterContract;
use Shy\Http\Contracts\View as ViewContract;
use Shy\Core\Contracts\Logger as LoggerContract;

use Shy\Http\Request;
use Shy\Http\Router;
use Shy\Http\View;
use Shy\Socket\Swoole\Session;
use Shy\Socket\Swoole\Response;

try {
    $container = Container::getContainer();
    $container->set('SHY_CYCLE_COUNT', 0);

    /**
     * Binding Contract
     */
    $container->binds([
        RequestContract::class => Request::class,
        ResponseContract::class => Response::class,
        SessionContract::class => Session::class,
        RouterContract::class => Router::class,
        ViewContract::class => View::class,
    ]);
    $container->aliases([
        'session' => SessionContract::class,
        'router' => RouterContract::class,
        'request' => RequestContract::class,
        'response' => ResponseContract::class,
        'view' => ViewContract::class,
    ]);

    /**
     * Update Dependencies
     */
    $container[LoggerContract::class]->setRequest($container->make(RequestContract::class));
    $container[HandlerRegister::class]->setView($container->make(ViewContract::class));
    $container[HandlerRegister::class]->setResponse($container->make(ResponseContract::class));

    $container->intelligentSchedulingOn();

    /**
     * Clear global variables
     */
    unset($container);

    /**
     * Load helper functions
     */
    require __DIR__ . '/../shy/Http/Functions/view.php';
} catch (Throwable $throwable) {
    echo implode(PHP_EOL, get_throwable_array($throwable));
    exit(1);
}
