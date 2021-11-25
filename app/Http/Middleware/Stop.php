<?php

namespace App\Http\Middleware;

use Shy\Core\Contract\Middleware;
use Closure;

class Stop implements Middleware
{
    public function handle(Closure $next, ...$passable)
    {
        echo 'App\Http\Middleware\Stop path param ';

        dd($passable);
    }
}
