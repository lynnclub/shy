<?php
/**
 * Shy Framework Http
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy;

use shy\core\pipeline;
use shy\http\request;
use shy\http\router;
use shy\http\response;

class http
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->systemSetting();

        if (config_key('illuminate_database')) {
            init_illuminate_database();
        }
    }

    /**
     * System setting
     */
    protected function systemSetting()
    {
        date_default_timezone_set(config_key('timezone'));
        /**
         * define
         */
        defined('BASE_PATH') or define('BASE_PATH', config_key('base', 'path'));
        defined('APP_PATH') or define('APP_PATH', config_key('app', 'path'));
        defined('VIEW_PATH') or define('VIEW_PATH', config_key('view', 'path'));
        defined('CACHE_PATH') or define('CACHE_PATH', config_key('cache', 'path'));
        defined('PUBLIC_PATH') or define('PUBLIC_PATH', config_key('public', 'path'));
        defined('SHY_PATH') or define('SHY_PATH', config_key('shy', 'path'));
        if (empty(config_key('base_url'))) {
            defined('BASE_URL') or define('BASE_URL', shy(request::class)->getBaseUrl());
        } else {
            defined('BASE_URL') or define('BASE_URL', config_key('base_url'));
        }
    }

    /**
     * Run router
     */
    public function runRouter()
    {
        logger('request', shy(request::class)->all());

        $response = shy(pipeline::class)
            ->send(shy(request::class))
            ->through(router::class)
            ->then(function ($response) {
                if (!empty($response)) {
                    shy(response::class)->send($response);
                }

                return $response;
            });

        $this->end();

        return $response;
    }

    /**
     * End handle
     */
    protected function end()
    {
        /**
         * Slow log
         */
        if (config_key('slow_log')) {
            $shyStartTime = config('SHY_CYCLE_START_TIME') ? config('SHY_CYCLE_START_TIME') : config('SHY_START_TIME');
            $usedTime = microtime(true) - $shyStartTime;
            if ($usedTime > config_key('slow_log_limit')) {
                $router = shy(router::class);

                logger('slow', [
                    'controller' => $router->getController(),
                    'method' => $router->getMethod(),
                    'middleware' => $router->getMiddleware(),
                    'usedTime' => $usedTime
                ], 'NOTICE');
            }
        }

        shy(request::class)->setInitFalse();
    }

}
