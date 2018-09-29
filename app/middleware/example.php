<?php

/**
 * Example Middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\middleware;

use Closure;
use shy\core\middleware;
use shy\http\facade\request;

class example implements middleware
{
    public function handle(Closure $next)
    {
        // request
        $request = request::getInstance();

        // run controller
        $response = $next($request);

        // response
        $response = 'example middleware' . json_encode($response);
        return $response;
    }
}
