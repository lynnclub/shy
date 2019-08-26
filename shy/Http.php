<?php

namespace Shy;

use Shy\Http\Contracts\Router as RouterContract;

class Http
{
    /**
     * Define
     */
    public function requestDefine()
    {
        if (!defined('BASE_URL')) {
            if (empty(config_key('base_url'))) {
                define('BASE_URL', shy('request')->getBaseUrl());
            } else {
                define('BASE_URL', config_key('base_url'));
            }
        }
    }

    /**
     * Run
     */
    public function run()
    {
        $request = shy('request');

        shy('logger')->info('Request', $request->all());

        $this->requestDefine();

        $response = shy('pipeline')
            ->send($request)
            ->through(RouterContract::class)
            ->then(function ($response) {
                if (!empty($response)) {
                    shy('response')->send($response);
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
        shy('request')->setInitializedFalse();

        /**
         * Slow log
         */
        if (config_key('slow_log')) {
            $startTime = shy()->has('SHY_CYCLE_START_TIME') ? shy()->get('SHY_CYCLE_START_TIME') : shy()->startTime();
            $usedTime = microtime(true) - $startTime;
            if ($usedTime > config_key('slow_log_limit')) {

                $router = shy(RouterContract::class);

                shy('logger')->notice('Slow', [
                    'controller' => $router->getController(),
                    'method' => $router->getMethod(),
                    'middleware' => $router->getMiddleware(),
                    'usedTime' => $usedTime
                ]);
            }
        }
    }

}
