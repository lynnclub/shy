<?php
/**
 * Example Middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace App\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Facades\Request;

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
