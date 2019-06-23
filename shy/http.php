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
use shy\http\session;

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
    }

    /**
     * Run
     */
    public function run()
    {
        $this->initRequest();

        /**
         * Run Router
         */
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
     * Init request
     */
    protected function initRequest()
    {
        shy(session::class)->sessionStart();
        $request = shy(request::class);
        $request->init($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
        logger('request: ' . json_encode($request->all()));

        if (empty(config_key('base_url'))) {
            defined('BASE_URL') or define('BASE_URL', $request->getBaseUrl());
        } else {
            defined('BASE_URL') or define('BASE_URL', config_key('base_url'));
        }
    }

    /**
     * End handle
     *
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
                $difference = [
                    'controller' => $router->getController(),
                    'method' => $router->getMethod(),
                    'middleware' => $router->getMiddleware(),
                    'usedTime' => $usedTime
                ];
                logger('slow: ' . json_encode($difference), 'NOTICE');
            }
        }
    }

}
