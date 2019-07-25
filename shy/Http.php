<?php

namespace Shy;

use Shy\Core\Container;
use Shy\Core\Contracts\Http as HttpContract;
use Shy\Http\Contracts\Router as RouterContract;

class Http extends Container implements HttpContract
{
    /**
     * Http constructor.
     */
    public function __construct()
    {
        static::setInstance($this);

        $this->set('SHY_START_TIME', microtime(true));

        if (isset($this['WebInWorkerMan'])) {
            $this->addForkedPidToStartId($this['WebInWorkerMan']->id);
        }
    }

    /**
     * Run router
     */
    public function runRouter()
    {
        $this['request']->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
        $this['session']->sessionStart();

        if (empty($this['config']->find('base_url'))) {
            defined('BASE_URL') or define('BASE_URL', $this['request']->getBaseUrl());
        } else {
            defined('BASE_URL') or define('BASE_URL', $this['config']->find('base_url'));
        }

        $this['logger']->info('request', $this['request']->all());

        $response = $this['pipeline']
            ->send($this['request'])
            ->through(RouterContract::class)
            ->then(function ($response) {
                if (!empty($response)) {
                    $this['response']->send($response);
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
//        if (config_key('slow_log')) {
//            $shyStartTime = config('SHY_CYCLE_START_TIME') ? config('SHY_CYCLE_START_TIME') : config('SHY_START_TIME');
//            $usedTime = microtime(true) - $shyStartTime;
//            if ($usedTime > config_key('slow_log_limit')) {
//                $router = shy(RouterContract::class);
//
//                $this['logger']->notice('slow', [
//                    'controller' => $router->getController(),
//                    'method' => $router->getMethod(),
//                    'middleware' => $router->getMiddleware(),
//                    'usedTime' => $usedTime
//                ]);
//            }
//        }

        $this['request']->setInitFalse();
    }

}
