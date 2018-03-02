<?php

/**
 * Common Function
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */


if (!function_exists('view')) {
    function view($filename, $compact = array())
    {
        //transfer parameter
        if (is_array($compact)) {
            foreach ($compact as $name => $value) {
                global $$name;
                $$name = $value;
            }
        }
        //parse template
        if (!empty($filename)) {
            $file = BASE_PATH . '/../app/views/' . $filename . '.php';
            if (file_exists($file)) {
                include_once BASE_PATH . '/../app/views/' . $filename . '.php';
            } else {
                header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
                echo 'The specified view file does not exist.';
                exit(1); // EXIT_ERROR
            }
        } else {
            header('HTTP/1.1 503 Service Unavailable.', TRUE, 503);
            echo 'No view file specified.';
            exit(1); // EXIT_ERROR
        }
        //unset parameter
        if (is_array($compact)) {
            foreach ($compact as $name => $value) {
                unset($GLOBALS[$$name]);
            }
        }
    }
}

if (!function_exists('get_base_url')) {
    function get_base_url()
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
        $base_url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
        if (dirname($_SERVER['SCRIPT_NAME']) !== DIRECTORY_SEPARATOR) {
            $base_url .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
        }
        return $base_url . '/';
    }
}



