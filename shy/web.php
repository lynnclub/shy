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
use Smarty;

class web
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->bind();
        $this->setting();
        if (config('smarty')) {
            $this->smartySetting();
        }
    }

    /**
     * Bind Object
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
     * System Setting
     */
    private function setting()
    {
        defined('BASE_PATH') or define('BASE_PATH', config('base', 'path'));
        defined('APP_PATH') or define('APP_PATH', config('app', 'path'));
        defined('CACHE_PATH') or define('CACHE_PATH', config('cache', 'path'));

        if (empty(config('base_url'))) {
            defined('BASE_URL') or define('BASE_URL', shy('request')->getSchemeAndHttpHost() . '/');
        } else {
            defined('BASE_URL') or define('BASE_URL', config('base_url'));
        }

        date_default_timezone_set(config('timezone'));
    }

    /**
     * Smarty Setting
     */
    private function smartySetting()
    {
        $smarty = shy('smarty', new Smarty());
        $smarty->template_dir = config('app', 'path') . 'http' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $smarty->compile_dir = config('cache', 'path') . 'app' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR;
        $smartyConfig = config('smarty_config');
        if (isset($smartyConfig['left_delimiter']) && !empty($smartyConfig['left_delimiter'])) {
            $smarty->left_delimiter = $smartyConfig['left_delimiter'];
        }
        if (isset($smartyConfig['right_delimiter']) && !empty($smartyConfig['right_delimiter'])) {
            $smarty->right_delimiter = $smartyConfig['right_delimiter'];
        }
        if (isset($smartyConfig['caching']) && is_bool($smartyConfig['caching'])) {
            $smarty->caching = $smartyConfig['caching'];
        }
        if (isset($smartyConfig['cache_lifetime']) && is_int($smartyConfig['cache_lifetime'])) {
            $smarty->cache_lifetime = $smartyConfig['cache_lifetime'];
        }
        if (config('env') === 'development') {
            $smarty->debugging = true;
        }
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
