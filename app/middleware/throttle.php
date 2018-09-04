<?php

/**
 * throttle
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\middleware;

use Closure;
use shy\core\middleware;

class throttle implements middleware
{
    public function handle($request, Closure $next)
    {
        echo 'throttle middleware<br>';

        $next($request);

        echo '<br>throttle after middleware';
    }
}