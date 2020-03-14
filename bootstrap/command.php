<?php

require __DIR__ . '/../vendor/autoload.php';

use Shy\Core\Container;
use Shy\Core\Exceptions\HandlerRegister;

use Shy\Core\Contracts\Config as ConfigContract;
use Shy\Core\Contracts\Logger as LoggerContract;
use Shy\Core\Contracts\ExceptionHandler as ExceptionHandlerContract;
use Shy\Core\Contracts\Pipeline as PipelineContract;
use Shy\Core\Contracts\Cache as CacheContract;
use Shy\Core\Contracts\DataBase as DataBaseContract;

use Shy\Core\Config;
use Shy\Core\Logger\File;
use Shy\Http\Exceptions\Handler;
use Shy\Core\Pipeline;
use Shy\Core\Cache\Memory;
use Shy\Core\DataBase\Pdo;

try {
    /**
     * Env and Config
     */
    $env = getenv('SHY_ENV');
    if (empty($env)) {
        $env = 'develop';
    }
    defined('SHY_ENV') or define('SHY_ENV', $env);

    unset($env);

    $config = new Config(dirname(__DIR__) . '/config/' . SHY_ENV . DIRECTORY_SEPARATOR);

    /**
     * Set Timezone
     */
    date_default_timezone_set($config->find('app.timezone'));

    /**
     * Define Constant
     */
    $path = $config->load('path');
    defined('BASE_PATH') or define('BASE_PATH', $path['base']);
    defined('APP_PATH') or define('APP_PATH', $path['app']);
    defined('VIEW_PATH') or define('VIEW_PATH', $path['view']);
    defined('CACHE_PATH') or define('CACHE_PATH', $path['cache']);
    defined('PUBLIC_PATH') or define('PUBLIC_PATH', $path['public']);

    unset($path);
    $config->delete('path');

    /**
     * Container
     */
    $container = Container::getContainer();
    $container->set(ConfigContract::class, $config)
        ->alias('config', ConfigContract::class);

    unset($config);

    /**
     * Binding Contract
     */
    $container->binds([
        LoggerContract::class => File::class,
        ExceptionHandlerContract::class => Handler::class,
        PipelineContract::class => Pipeline::class,
        CacheContract::class => Memory::class,
        DataBaseContract::class => Pdo::class,
    ]);

    /**
     * Registering Exception Handler Through Dependency Injection
     *
     * @dependency ExceptionHandlerContract
     * @dependency ConfigContract
     * @dependency LoggerContract
     * @dependency ResponseContract
     */
    $container->make(HandlerRegister::class);

    unset($container);
} catch (Throwable $throwable) {
    echo implode(PHP_EOL, get_throwable_array($throwable));
    exit(1);
}
