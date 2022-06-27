<?php

namespace Shy\Http\Middleware;

use Closure;
use Shy\Contract\Middleware;
use Shy\Http\Facade\Request;
use Shy\Http\Exception\HttpException;

class CSRF implements Middleware
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
        $token = Request::header('X-CSRF-TOKEN', '');
        if (empty($token)) {
            $token = Request::get('csrf-token', '');
            if (empty($token)) {
                $token = Request::get('_token', '');
            }
        }

        if (!csrf_verify($token, $passable[0] ?? '')) {
            if (Request::expectsJson()) {
                return get_response_json(5002);
            } else {
                throw new HttpException(403, lang(5002));
            }
        }

        return $next();
    }
}
