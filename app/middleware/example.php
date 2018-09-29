<?php

/**
 * Example Middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\middleware;

use shy\core\middleware;

class example implements middleware
{
    public function handle(...$passable)
    {
        $next = reset($passable);
        // request handle
        $request = null;

        // run controller
        $response = $next();

        // response handle
        $response = ', example middleware, ' . json_encode($response);
        return $response;
    }
}
