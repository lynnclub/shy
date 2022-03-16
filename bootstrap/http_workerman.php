<?php

use Shy\Container;
use Shy\Exception\HandlerRegister;

// Contracts
use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\Contract\Response as ResponseContract;
use Shy\Http\Contract\Session as SessionContract;
use Shy\Http\Contract\Router as RouterContract;
use Shy\Http\Contract\View as ViewContract;
use Shy\Contract\Logger as LoggerContract;

// Entry
use Shy\Http\Request;
use Shy\Http\Router;
use Shy\Http\View;
use Shy\Socket\WorkerMan\Session;
use Shy\Socket\WorkerMan\Response;

try {
    //Container already started in Command
    $container = Container::getContainer();
    $container->set('SHY_CYCLE_COUNT', 0);

    //Binding Addition Contract
    $container->binds([
        RequestContract::class => Request::class,
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

    //Update Dependencies
    $container[LoggerContract::class]->setRequest($container->make(RequestContract::class));
    $container[HandlerRegister::class]->setView($container->make(ViewContract::class))
        ->setResponse($container->make(ResponseContract::class));

    //$container->intelligentSchedulingOn();

    //Loading files
    require __DIR__ . '/../shy/Http/Function/view.php';
    require __DIR__ . '/../app/Function/common.php';

    return $container;
} catch (Throwable $throwable) {
    echo implode(PHP_EOL, get_throwable_array($throwable));
    exit(1);
}
