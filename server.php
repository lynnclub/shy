<?php
/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

defined('IS_CLI') or define('IS_CLI', is_int(strpos(php_sapi_name(), 'cli')) ? true : false);

use shy\http\exception\handler;

if (IS_CLI) {
    global $_SHY_START, $_CYCLE_COUNT;
    $_SHY_START = microtime(true);
    $_CYCLE_COUNT += 1;

    /**
     * Run Framework In CLI
     */
    shy('http')->run();
} else {
    define('SHY_START', microtime(true));

    /**
     * Composer Autoload
     */
    require __DIR__ . '/vendor/autoload.php';

    /**
     * Helpers
     */
    require __DIR__ . '/shy/core/function/helpers.php';
    require __DIR__ . '/shy/http/function/view.php';

    /**
     * Run Framework
     */
    (container())->setExceptionHandler(new handler());
    shy('http', 'shy\http')->run();
}
