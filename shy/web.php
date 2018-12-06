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
    /**
     * web constructor.
     */
    public function __construct()
    {
        $this->bind();
        $this->setting();
    }

    /**
     * Bind object ready to join container
     */
    private function bind()
    {
        bind('view', new view());
        bind('pipeline', new pipeline());
        bind('request', new request($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input')));
        bind('router', new router());
        bind('response', new response());
    }

    /**
     * System setting
     */
    private function setting()
    {
        defined('BASE_URL') or define('BASE_URL', config('base_url'));
        defined('BASE_PATH') or define('BASE_PATH', config('base', 'path'));
        defined('APP_PATH') or define('APP_PATH', config('app', 'path'));
        defined('CACHE_PATH') or define('CACHE_PATH', config('cache', 'path'));

        date_default_timezone_set(config('timezone'));
    }

    /**
     * Run
     */
    public function run()
    {
        logger('request/', serialize(shy('request')));

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

    /**
     * End
     *
     * @param $response
     */
    public function end($response)
    {
        /**
         * slow_log
         */
        if (config('slow_log')) {
            $difference = microtime(true) - SHY_START;
            if ($difference > config('slow_log_limit')) {
                logger('slowLog/log', json_encode([
                    'controller' => shy('router')->getController(),
                    'method' => shy('router')->getMethod(),
                    'difference' => $difference
                ]));
            }
        }

        logger('response/', serialize($response));
    }

}
