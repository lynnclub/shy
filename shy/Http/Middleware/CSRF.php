<?php

namespace Shy\Http\Middleware;

use Closure;
use Shy\Core\Contract\Middleware;
use Shy\Http\Facades\Request;
use Shy\Http\Facades\Session;
use Shy\Http\Exceptions\HttpException;

class CSRF implements Middleware
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
        $token = Request::header('X-CSRF-TOKEN');
        if (empty($token)) {
            $token = Request::get('csrf-token');
            if (empty($token)) {
                $token = Request::get('_token');
            }
        }

        if (empty($token) || $token !== Session::get('csrf-token')) {
            if (Request::expectsJson()) {
                return get_response_json(5002);
            } else {
                throw new HttpException(403, lang(5002));
            }
        }

        return $next();
    }
}
