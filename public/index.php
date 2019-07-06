<?php

/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

use shy\http\request;
use shy\http\session;
use shy\http;

/**
 * Is in cli cycle
 */
if (function_exists('shy')) {
    container()->forkProcessNoAddToStartId(shy(shy\webInWorkerMan::class)->id);
    config_del('SHY_CYCLE_START_TIME');
    config_set('SHY_CYCLE_START_TIME', microtime(true));
    config_int_calc('SHY_CYCLE_COUNT');
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
shy(request::class)->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
shy(session::class)->sessionStart();
shy(http::class)->runRouter();
