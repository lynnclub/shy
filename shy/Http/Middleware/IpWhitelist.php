<?php

namespace Shy\Http\Middleware;

use Closure;
use Shy\Contract\Middleware;
use Shy\Http\Facade\Request;
use Shy\Facade\Logger;
use Shy\Http\Exception\HttpException;

class IpWhitelist implements Middleware
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
        $hit = FALSE;

        $whitelist = config('ip_whitelist');
        if (is_array($whitelist)) {
            $userIps = Request::getClientIps();
            foreach ($userIps as $userIp) {
                if (in_array($userIp, $whitelist)) {
                    $hit = TRUE;
                }
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

        return $next();
    }
}
