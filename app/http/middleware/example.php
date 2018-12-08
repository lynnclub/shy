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

class example implements middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = null;

        // run controller
        $response = $next();

        // response handle
        $response = ', example middleware, ' . json_encode($response);
        return $response;
    }
}
