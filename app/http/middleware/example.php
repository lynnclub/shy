<?php
/**
 * Example Middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\http\middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Facades\Request;

class Example implements Middleware
{
    public function handle(Closure $next, ...$passable)
    {
        // request handle
        $request = request::all();

        // run controller
        $response = $next();

        // response handle
        $response = 'request: ' . json_encode($request) . ', example middleware response: ' . $response;

        return $response;
    }
}
