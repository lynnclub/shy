<?php
/**
 * csrf(waiting)
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http\middleware;

use shy\core\middleware;
use Closure;
use shy\http\facade\request;

class csrf implements middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = request::all();

        // run controller
        $response = $next();

        // response handle
        $response = 'request: ' . json_encode($request) . ', csrf middleware response:' . $response;

        return $response;
    }
}
