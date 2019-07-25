<?php

/**
 * Shy Framework Console Bootstrap
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */


$http = new Shy\Console();

new Shy\Core\Exceptions\HandlerRegister(new Shy\Http\Exceptions\Handler());

/**
 * Load helpers
 */


/**
 * Base define
 */
defined('ENVIRONMENT') or define('ENVIRONMENT', empty(getenv('ENVIRONMENT')) ? 'local' : getenv('ENVIRONMENT'));
defined('CONFIG_DIR') or define('CONFIG_DIR', __DIR__ . '/config/' . ENVIRONMENT . DIRECTORY_SEPARATOR);

/**
 * New container and set exception handler
 */
container(CONFIG_DIR)->setExceptionHandler();
