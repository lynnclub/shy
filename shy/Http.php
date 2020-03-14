<?php

namespace Shy;

use Shy\Core\Contracts\Logger;
use Shy\Core\Contracts\Pipeline;

class Http
{
    public function run()
    {
        $request = shy('request');

        shy(Logger::class)->info('Request', $request->all());

        $this->defineRequestConstant($request);

        $response = shy(Pipeline::class)
            ->send($request)
            ->through('router')
            ->then(function ($response) {
                if (!empty($response)) {
                    shy('response')->send($response);
                }

                return $response;
            });

        $request->setInitializedFalse();

        return $response;
    }

    protected function defineRequestConstant($request)
    {
        if (!defined('BASE_URL')) {
            if (empty(config('app.base_url'))) {
                define('BASE_URL', $request->getBaseUrl());
            } else {
                define('BASE_URL', config('app.base_url'));
            }
        }
    }
}
