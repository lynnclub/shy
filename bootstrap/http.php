<?php

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

use Shy\Core\Container;
use Shy\Core\Exception\HandlerRegister;

// Contracts
use Shy\Core\Contract\Config as ConfigContract;
use Shy\Core\Contract\Logger as LoggerContract;
use Shy\Core\Contract\ExceptionHandler as ExceptionHandlerContract;
use Shy\Core\Contract\Pipeline as PipelineContract;
use Shy\Core\Contract\Cache as CacheContract;
use Shy\Core\Contract\DataBase as DataBaseContract;
use Shy\Http\Contracts\Request as RequestContract;
use Shy\Http\Contracts\Response as ResponseContract;
use Shy\Http\Contracts\Session as SessionContract;
use Shy\Http\Contracts\Router as RouterContract;
use Shy\Http\Contracts\View as ViewContract;

// Entry
use Shy\Core\Config;
use Shy\Core\Logger\File;
use Shy\Http\Exceptions\Handler;
use Shy\Core\Pipeline;
use Shy\Core\Cache\Redis;
use Shy\Core\DataBase\Illuminate;
use Shy\Http\Request;
use Shy\Http\Response;
use Shy\Http\Session;
use Shy\Http\Router;
use Shy\Http\View;

//Set Environment
$env = getenv('SHY_ENV');
defined('SHY_ENV') or define('SHY_ENV', empty($env) ? 'develop' : $env);
unset($env);

try {
    //Define Constants
    defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
    defined('APP_PATH') or define('APP_PATH', BASE_PATH . 'app' . DIRECTORY_SEPARATOR);
    defined('CACHE_PATH') or define('CACHE_PATH', BASE_PATH . 'cache' . DIRECTORY_SEPARATOR);
    defined('PUBLIC_PATH') or define('PUBLIC_PATH', BASE_PATH . 'public' . DIRECTORY_SEPARATOR);
    defined('VIEW_PATH') or define('VIEW_PATH', APP_PATH . 'Http' . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR);

    //Container Initialization
    $container = Container::getContainer();
    $container->binds([
        ConfigContract::class => Config::class,
        LoggerContract::class => File::class,
        ExceptionHandlerContract::class => Handler::class,
        PipelineContract::class => Pipeline::class,
        CacheContract::class => Redis::class,
        DataBaseContract::class => Illuminate::class,
        RequestContract::class => Request::class,
        ResponseContract::class => Response::class,
        SessionContract::class => Session::class,
        RouterContract::class => Router::class,
        ViewContract::class => View::class,
    ])->aliases([
        'config' => ConfigContract::class,
        'session' => SessionContract::class,
        'request' => RequestContract::class,
        'response' => ResponseContract::class,
        'view' => ViewContract::class,
    ]);

    //Make Config
    $container->make(
        ConfigContract::class,
        BASE_PATH . 'config',
        SHY_ENV,
        CACHE_PATH . 'app/config.cache'
    );

    date_default_timezone_set($container['config']->find('app.timezone'));

    /**
     * Registering Exception Handler Through Dependency Injection
     *
     * @dependency ExceptionHandlerContract
     * @dependency ConfigContract
     * @dependency LoggerContract
     * @dependency ResponseContract
     */
    $container->make(HandlerRegister::class);

    //Loading files
    require __DIR__ . '/../shy/Http/Function/view.php';
    require __DIR__ . '/../app/Function/common.php';

    return $container;
} catch (\Throwable $throwable) {
    echo implode('<br>', get_throwable_array($throwable));
    exit(1);
}
