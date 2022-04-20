<?php

namespace Shy\Http\Middleware;

use Closure;
use Shy\Contract\Middleware;
use Shy\Http\Facade\Request;
use Shy\Facade\Cache;
use Shy\Facade\Logger;
use Shy\Http\Facade\Response;
use Shy\Http\Facade\Router;
use Shy\Http\Exception\HttpException;

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
        $limitTimes = 60;
        if (isset($passable[0]) && is_numeric($passable[0])) {
            $limitTimes = $passable[0];
        }

        $limitMinute = $coolDownMinute = 1;
        if (isset($passable[1]) && is_numeric($passable[1])) {
            $coolDownMinute = $passable[1];
        }

        $userIps = Request::getClientIps();
        $cacheKey = 'throttle:count:' . Router::getController() . ':' . Router::getMethod() . ':ip:';

        $time = time();
        foreach ($userIps as $userIp) {
            $cache = json_decode(Cache::get($cacheKey . $userIp), TRUE);

            if (isset($cache['count'], $cache['time']) && $cache['time'] > $time) {
                $cache['count'] += 1;
            } else {
                $cache = [
                    'count' => 1,
                    'time' => $time + ($coolDownMinute * 60)
                ];
            }

            $remaining = $limitTimes - $cache['count'];
            $headers = [
                'X-RateLimit-Limit:' . $limitTimes,
                'X-RateLimit-Remaining:' . ($remaining < 0 ? 0 : $remaining)
            ];

            if ($remaining <= 0) {
                $limitMinute = $coolDownMinute;
            }
            Cache::set($cacheKey . $userIp, json_encode($cache), $limitMinute * 60);

            if ($remaining <= 0) {
                Logger::notice('Throttle block request', Request::all());

                $headers[] = 'X-RateLimit-Retry-After:' . ($cache['time'] - $time);

                if (Request::expectsJson()) {
                    Response::withHeaders($headers);
                    return get_response_json(5001);
                } else {
                    throw new HttpException(403, lang(5001), null, $headers);
                }
            } else {
                Response::withHeaders($headers);
            }
        }

        return $next();
    }
}
