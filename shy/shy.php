<?php
/**
 * Shy Framework
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

define('BASE_PATH', dirname(__FILE__));
require_once BASE_PATH . '/func/common.php';
require_once BASE_PATH . '/../vendor/autoload.php';

use config\app;
use shy\router;

/**
 * Error Reporting
 */
switch (app::ENVIRONMENT) {
    case 'development':
        error_reporting(-1);
        ini_set('display_errors', 1);
        break;

    case 'testing':
    case 'production':
        ini_set('display_errors', 0);
        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
        } else {
            error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE);
        }
        break;

    default:
        header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
        echo 'The application environment is not set correctly.';
        exit(1); // EXIT_ERROR
}

/**
 * Time Zone
 */
date_default_timezone_set(app::TIMEZONE);

/**
 * Base information:
 *   1. BASE_PATH
 *   2. BASE_URL
 *   3. SOCKET
 */
define('BASE_URL', get_base_url());

new router();
