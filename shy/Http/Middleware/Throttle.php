<?php

namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Facades\Request;
use Shy\Core\Facades\Cache;
use Shy\Core\Facades\Logger;
use Shy\Http\Facades\Response;
use Shy\Http\Exceptions\HttpException;

class Throttle implements Middleware
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
        $limit = 60;
        if (isset($passable[0]) && is_numeric($passable[0])) {
            $limit = $passable[0];
        }

        $coolDownMinute = 1;
        if (isset($passable[1]) && is_numeric($passable[1])) {
            $coolDownMinute = $passable[1];
        }

        $userIps = Request::getClientIps();

        $time = time();
        foreach ($userIps as $userIp) {
            $cache = Cache::get('throttle:count:ip:' . $userIp);

            if (isset($cache['count'], $cache['time']) && $cache['time'] > $time) {
                $cache['count'] += 1;
            } else {
                $cache = [
                    'count' => 1,
                    'time' => $time + ($coolDownMinute * 60)
                ];
            }

            Cache::set('throttle:count:ip:' . $userIp, $cache, $coolDownMinute * 60);

            $remaining = $limit - $cache['count'];
            if ($remaining < 0) {
                $remaining = 0;
            }
            $header = ['X-RateLimit-Limit:' . $limit, 'X-RateLimit-Remaining:' . $remaining];
            Response::setHeader($header);

            if ($cache['count'] > $limit) {
                Logger::info('Throttle block request', Request::all());

                $header[] = 'X-RateLimit-Retry-After:' . ($cache['time'] - $time);

                if (Request::ajax()) {
                    Response::setHeader($header);
                    return get_response_json(5001);
                } else {
                    throw new HttpException(403, lang(5001), null, $header);
                }
            }
        }

        // run controller
        return $next();
    }

}
