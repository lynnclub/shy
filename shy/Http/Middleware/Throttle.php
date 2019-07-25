<?php

/**
 * throttle(waiting)
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;

class Throttle implements middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = null;

        // run controller
        $response = $next();

        // response handle
        $response = ' example middleware ' . json_encode($response);
        return $response;
    }
}