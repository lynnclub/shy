<?php
/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

defined('ENVIRONMENT') or define('ENVIRONMENT', empty(getenv('ENVIRONMENT')) ? 'local' : getenv('ENVIRONMENT'));
defined('CONFIG_DIR') or define('CONFIG_DIR', __DIR__ . '/../config/' . ENVIRONMENT . DIRECTORY_SEPARATOR);

use shy\webInWorkerMan;
use shy\http\request;
use shy\http\session;
use shy\http;
use shy\http\exception\handler;

if (function_exists('shy')) {
    container()->forkProcessNoAddToStartId(shy(webInWorkerMan::class)->id);
    config_del('SHY_CYCLE_START_TIME');
    config_set('SHY_CYCLE_START_TIME', microtime(true));
    config_int_calc('SHY_CYCLE_COUNT');
    /**
     * Run Framework In CLI mode
     */
    shy(request::class)->initInput($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
    shy(session::class)->sessionStart();
    shy(http::class)->runRouter();
} else {
    /**
     * Composer Autoload
     */
    require __DIR__ . '/../vendor/autoload.php';
    /**
     * Helpers
     */
    require __DIR__ . '/../shy/core/function/core.php';
    require __DIR__ . '/../shy/core/function/helpers.php';
    require __DIR__ . '/../shy/http/function/view.php';
    /**
     * Run Framework
     */
    container(CONFIG_DIR)->setExceptionHandler(new handler());
    shy(request::class)->initInput($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
    shy(session::class)->sessionStart();
    shy(http::class)->runRouter();
}
