<?php
/**
 * Shy Framework Web
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy;

use shy\core\pipeline;
use shy\http\request;
use shy\http\router;
use shy\http\view;
use shy\http\response;

class web
{
    public function __construct()
    {
        $this->make();
        $this->setting();
    }

    private function make()
    {
        bind('view', new view());
        bind('pipeline', new pipeline());
        bind('request', new request($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER));
        bind('router', new router());
        bind('response', new response());
    }

    private function setting()
    {
        /**
         * Time Zone
         */
        date_default_timezone_set(config('timezone'));
    }

    public function run()
    {
        shy('pipeline')
            ->send(shy('request'))
            ->through('router')
            ->then(function ($response) {
                if (!empty($response)) {
                    shy('response')->send($response);
                }
                $this->end($response);
            });
    }

    public function end($response)
    {
        //echo 'end';
    }

}
