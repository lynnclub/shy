<?php

use Shy\Container;
use Shy\Exception\HandlerRegister;

// 契约 Contract
use Shy\Contract\Config as ConfigContract;
use Shy\Contract\Logger as LoggerContract;
use Shy\Contract\ExceptionHandler as ExceptionHandlerContract;
use Shy\Contract\Pipeline as PipelineContract;
use Shy\Contract\Cache as CacheContract;
use Shy\Contract\DataBase as DataBaseContract;

// 组件 Component
use Shy\Config;
use Shy\Logger\File;
use Shy\Http\Exception\Handler;
use Shy\Pipeline;
use Shy\Cache\Memory;
use Shy\DataBase\Illuminate;

// 自动加载 Composer
require __DIR__ . '/../vendor/autoload.php';

// 设置环境 Environment
if (!defined('SHY_ENV')) {
    if (!$env = getenv('SHY_ENV')) {
        $env = 'develop';
        putenv('SHY_ENV=develop');
    }

    define('SHY_ENV', $env);
}

try {
    // 定义常量 Constant
    defined('BASE_PATH') or define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
    defined('APP_PATH') or define('APP_PATH', BASE_PATH . 'app' . DIRECTORY_SEPARATOR);
    defined('CACHE_PATH') or define('CACHE_PATH', BASE_PATH . 'cache' . DIRECTORY_SEPARATOR);
    defined('PUBLIC_PATH') or define('PUBLIC_PATH', BASE_PATH . 'public' . DIRECTORY_SEPARATOR);
    defined('VIEW_PATH') or define('VIEW_PATH', APP_PATH . 'Http' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR);

    // 容器初始化 Container initialization
    $container = Container::getContainer();
    $container->binds([
        ConfigContract::class => Config::class,
        LoggerContract::class => File::class,
        ExceptionHandlerContract::class => Handler::class,
        PipelineContract::class => Pipeline::class,
        CacheContract::class => Memory::class,
        DataBaseContract::class => Illuminate::class,
    ])->aliases([
        'config' => ConfigContract::class,
    ]);

    // 启动配置组件 Startup config
    $container->make(
        ConfigContract::class,
        BASE_PATH . 'config',
        SHY_ENV,
        CACHE_PATH . 'app/config.cache'
    );

    // 设置时区 TimeZone
    date_default_timezone_set($container['config']->find('app.timezone'));

    /**
     * 通过依赖注入，注册异常处理
     * Register exception handler through Dependency injection
     *
     * @dependency ExceptionHandlerContract
     * @dependency ConfigContract
     * @dependency LoggerContract
     * @dependency ResponseContract
     */
    $container->make(HandlerRegister::class);

    // 加载文件 Loading files
    require __DIR__ . '/../app/Function/common.php';

    return $container;
} catch (\Throwable $throwable) {
    echo implode(PHP_EOL, get_throwable_array($throwable));
    exit(1);
}
