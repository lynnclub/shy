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
use shy\http\exception\handler;

$_container = new container();
$_container->setExceptionHandler(new handler());
shy('shy\web')->run();
