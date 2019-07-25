<?php
/**
 * csrf(waiting)
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Facades\Request;

class Csrf implements Middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = Request::all();

        // run controller
        $response = $next();

        // response handle
        $response = 'request: ' . json_encode($request) . ', csrf middleware response:' . $response;

        return $response;
    }
}
