<?php

namespace App\Http\Middleware;

use Shy\Contract\Middleware;
use Closure;
use Shy\Http\Facade\Request;

class Example implements Middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = Request::all();

        // run controller
        $response = $next();

        // response handle
        $response = 'request: ' . json_encode($request) . ', example middleware response: ' . $response;

        return $response;
    }
}
