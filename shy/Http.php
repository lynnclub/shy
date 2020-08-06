<?php

namespace Shy;

use Shy\Http\Contracts\Request;
use Shy\Core\Contracts\Logger;
use Shy\Core\Contracts\Pipeline;
use Shy\Http\Contracts\Response;

class Http
{
    public function run()
    {
        $request = shy(Request::class);

        shy(Logger::class)->info('Request', $request->all());

        if (!defined('BASE_URL')) {
            if (empty(config('app.base_url'))) {
                define('BASE_URL', $request->getBaseUrl());
            } else {
                define('BASE_URL', config('app.base_url'));
            }
        }

        shy(Pipeline::class)
            ->send($request)
            ->through('router')
            ->then(function ($body) {
                if ($body instanceof Response) {
                    $body->output();
                } else {
                    shy(Response::class)->output($body);
                }
            });

        $request->initialize();
    }
}
