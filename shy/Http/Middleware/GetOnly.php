<?php

namespace Shy\Http\Middleware;

use Closure;
use Shy\Contract\Middleware;
use Shy\Http\Facade\Request;
use Shy\Http\Exception\HttpException;

class GetOnly implements Middleware
{
    /**
     * Handle
     *
     * @param Closure $next
     * @param ...$passable
     * @return mixed|string
     */
    public function handle(Closure $next, ...$passable)
    {
        if (strtoupper(Request::getMethod()) !== 'GET') {
            throw new HttpException(404);
        }

        return $next();
    }
}
