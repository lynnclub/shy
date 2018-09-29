<?php

/**
 * throttle(waiting)
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http\middleware;

use shy\core\middleware;

class throttle implements middleware
{
    public function handle(...$passable)
    {
        $next = reset($passable);
        // request handle
        $request = null;

        // run controller
        $response = $next();

        // response handle
        $response = ' example middleware ' . json_encode($response);
        return $response;
    }
}