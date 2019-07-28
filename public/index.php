<?php

/**
 * Shy Framework Http Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

/**
 * Composer autoload
 */
require __DIR__ . '/../vendor/autoload.php';

/**
 * Bootstrap
 */
require __DIR__ . '/../bootstrap/http.php';

/**
 * Run framework
 */
shy('request')->initialize($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER, file_get_contents('php://input'));
shy('session')->sessionStart();
shy('http')->run();
