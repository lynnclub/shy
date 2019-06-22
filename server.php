<?php
/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

use shy\http\exception\handler;

if (function_exists('shy')) {
    config_del('SHY_CYCLE_START_TIME');
    config_set('SHY_CYCLE_START_TIME', microtime(true));
    config_int_calc('SHY_CYCLE_COUNT');
    /**
     * Run Framework In CLI mode
     */
    shy('http')->run();
} else {
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
     * Config
     */
    config_set('SHY_START_TIME', microtime(true));
    /**
     * Run Framework
     */
    (container())->setExceptionHandler(new handler());
    shy('http', 'shy\http')->run();
}
