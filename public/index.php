<?php
/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

defined('ENVIRONMENT') or define('ENVIRONMENT', empty(getenv('ENVIRONMENT')) ? 'local' : getenv('ENVIRONMENT'));
defined('CONFIG_DIR') or define('CONFIG_DIR', __DIR__ . '/../config/' . ENVIRONMENT . DIRECTORY_SEPARATOR);

use shy\http;
use shy\http\exception\handler;

if (function_exists('shy')) {
    config_del('SHY_CYCLE_START_TIME');
    config_set('SHY_CYCLE_START_TIME', microtime(true));
    config_int_calc('SHY_CYCLE_COUNT');
    /**
     * Run Framework In CLI mode
     */
    shy(http::class)->run();
} else {
    /**
     * Composer Autoload
     */
    require __DIR__ . '/../vendor/autoload.php';
    /**
     * Helpers
     */
    require __DIR__ . '/../shy/core/function/helpers.php';
    require __DIR__ . '/../shy/http/function/view.php';
    /**
     * Run Framework
     */
    (container(CONFIG_DIR))->setExceptionHandler(new handler());
    shy(http::class)->run();
}
