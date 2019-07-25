<?php

/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */


/**
 * If is in cli mode cycling
 */
if (function_exists('shy')) {
    /**
     * Cycle start time
     */
    shy()->set('SHY_CYCLE_START_TIME', microtime(true));
} else {
    /**
     * Composer autoload
     */
    require __DIR__ . '/../vendor/autoload.php';

    /**
     * Bootstrap
     */
    require __DIR__ . '/../bootstrap/http.php';
}

/**
 * Run framework
 */

shy()->runRouter();
