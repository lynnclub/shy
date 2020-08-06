<?php

namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Facades\Request;
use Shy\Core\Facades\Logger;
use Shy\Http\Exceptions\HttpException;

class IpWhitelist implements Middleware
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
        $hit = FALSE;

        $userIps = Request::getClientIps();

        foreach ($userIps as $userIp) {
            if (in_array($userIp, config('ip_whitelist'))) {
                $hit = TRUE;
            }
        }

        if (!$hit) {
            Logger::info('Ip whitelist block request', Request::all());

            if (Request::expectsJson()) {
                return get_response_json(5000);
            } else {
                throw new HttpException(403, lang(5000));
            }
        }

        // run controller
        return $next();
    }

}
