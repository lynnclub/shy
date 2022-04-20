<?php

namespace App\Command;

use Shy\Contract\Pipeline;
use Shy\Http\Contract\Request;
use Shy\Http\Request as RealRequest;
use App\Http\Middleware\Example as ExampleMiddleware;

class Example
{
    public function test()
    {
        return 'Just for fun';
    }

    public function test2()
    {
        shy(Request::class, RealRequest::class);

        $response = shy(Pipeline::class)
            ->send(1, 2, 4)
            ->through([ExampleMiddleware::class, ExampleMiddleware::class])
            ->then(function () {
                return '$this->runController()';
            });

        return $response;
    }
}
