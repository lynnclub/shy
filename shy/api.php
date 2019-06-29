<?php
/**
 * Shy Framework api
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy;

use shy\core\pipeline;
use shy\http\request;
use shy\http\router;
use shy\http\response;

class api
{
    public function __construct()
    {
        $this->setting();
    }

    private function setting()
    {
        /**
         * Time Zone
         */
        date_default_timezone_set(config_key('timezone'));
    }

    public function run()
    {
        shy(pipeline::class)
            ->send(shy(request::class))
            ->through(router::class)
            ->then(function ($response) {
                if (!empty($response)) {
                    shy(response::class)->send($response);
                }

                return $response;
            });

        $this->end();
    }

    public function end()
    {
        shy(request::class)->setInitFalse();
    }

}
