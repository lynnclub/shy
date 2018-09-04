<?php
/**
 * Shy Framework Web Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

define('SHY_START', microtime(true));

/**
 * Composer Autoload
 */
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Helpers
 */
require_once __DIR__ . '/../shy/function/helpers.php';

/**
 * Framework
 */

use shy\core\container;
use shy\core\config;
use shy\exception\webHandler;
use shy\facade\exceptionHandler;
use shy\web;

$_container = new container();

$_container->bindObject('config', new config());
$_container->bindObject('exception\handler', new webHandler());
$_container->setExceptionHandler(exceptionHandler::getInstance(), config('env'));
$_container->bindObject('web', new web())->run();
