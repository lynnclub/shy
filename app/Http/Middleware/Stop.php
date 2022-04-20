<?php

namespace App\Http\Middleware;

use Closure;
use Shy\Contract\Middleware;

class Stop implements Middleware
{
    public function handle(Closure $next, ...$passable)
    {
        echo 'App\Http\Middleware\Stop path param ';

        return $passable;
    }
}
