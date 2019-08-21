<?php
/**
 * IP Baffle
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace Shy\Http\Middleware;

use Shy\Core\Contracts\Middleware;
use Closure;
use Shy\Http\Exceptions\HttpException;
use Shy\Http\Facades\Request;
use Shy\Core\Facades\Logger;

class IpBaffle implements Middleware
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
        $hit = false;

        $userIps = Request::getClientIps();

        foreach ($userIps as $userIp) {
            if (in_array($userIp, config('ip_baffle'))) {
                $hit = true;
            }
        }

        if (!$hit) {
            Logger::info('Baffle block request', Request::all());

            if (Request::ajax()) {
                return get_response_json(5000);
            } else {
                throw new HttpException(403, lang(5000));
            }
        }

        // run controller
        return $next();
    }

}
