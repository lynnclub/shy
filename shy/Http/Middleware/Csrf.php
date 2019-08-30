<?php

namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Facades\Request;
use Shy\Http\Facades\Session;
use Shy\Http\Exceptions\HttpException;

class Csrf implements Middleware
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
        if (Request::headers()->has('X-CSRF-TOKEN')) {
            $token = Request::headers()->get('X-CSRF-TOKEN');
        } else {
            $token = Request::get('_token');
        }

        if (empty($token) || $token !== Session::get('__token')) {
            if (Request::ajax()) {
                return get_response_json(5002);
            } else {
                throw new HttpException(403, lang(5002));
            }
        }

        // run controller
        return $next();
    }

}
