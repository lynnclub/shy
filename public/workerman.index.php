<?php
/**
 * Shy Framework WorkerMan Web Entry
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

/**
 * Run Framework
 */
global $_SHY_START;
$_SHY_START = microtime(true);

shy('web')->run();
