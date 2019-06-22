<?php

/**
 * Example Middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\http\middleware;

use shy\core\middleware;
use Closure;
use shy\http\facade\request;

class example implements middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = request::all();

        // run controller
        $response = $next();

        // response handle
        $response = 'request: ' . json_encode($request) . ', example middleware, response: ' . json_encode($response);
        return $response;
    }
}
