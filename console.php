<?php
/**
 * Shy Framework Console Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

define('SHY_START', microtime(true));

/**
 * Composer Autoload
 */
require_once __DIR__ . '/vendor/autoload.php';

/**
 * Helpers
 */
require_once __DIR__ . '/shy/helpers.php';

/**
 * Framework Run
 */

use shy\core\container;
use shy\core\pipeline;
use shy\web;

$_container = new container();
$_container->bind('pipeline', new pipeline());
$_container->bind('web', new web());

shy('web')->run();
