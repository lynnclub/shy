<?php

namespace Shy\Http\Middleware;

use Closure;
use Shy\Core\Contracts\Middleware;
use Shy\Http\Facades\Request;
use Shy\Http\Exceptions\HttpException;

class PostOnly implements Middleware
{
    /**
     * Handle
     *
     * @param Closure $next
     * @param array ...$passable
     * @return mixed|string
     */
    public function handle(Closure $next, ...$passable)
    {
        if (strtoupper(Request::getMethod()) !== 'POST') {
            throw new HttpException(404);
        }

        return $next();
    }
}
