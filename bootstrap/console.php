<?php

/**
 * Shy Framework Console Bootstrap
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

/**
 * Load helpers
 */
require __DIR__ . '/../shy/core/function/core.php';
require __DIR__ . '/../shy/core/function/helpers.php';

/**
 * Base define
 */
defined('ENVIRONMENT') or define('ENVIRONMENT', empty(getenv('ENVIRONMENT')) ? 'local' : getenv('ENVIRONMENT'));
defined('CONFIG_DIR') or define('CONFIG_DIR', __DIR__ . '/config/' . ENVIRONMENT . DIRECTORY_SEPARATOR);

/**
 * New container and set exception handler
 */
container(CONFIG_DIR)->setExceptionHandler();
