<?php

/**
 * Common Function
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('config')) {
    /**
     * Get Config
     *
     * @param $key
     * @param $filename
     * @return mixed|bool
     */
    function config($key, $filename = 'app')
    {
        global $config;
        if (!isset($config[$filename])) {
            $config[$filename] = require_once BASE_PATH . 'config/' . $filename . '.php';
        }
        if (isset($config[$filename][$key])) {
            return $config[$filename][$key];
        }

        return false;
    }
}

if (!function_exists('showError')) {
    /**
     * Show Error
     *
     * @param int $code
     * @param string $contentMsg
     */
    function showError($code = 404, $contentMsg = 'error')
    {
        $httpCode = httpCodeMessage($code);
        header($httpCode['msg'], TRUE, $httpCode['code']);
        if ($httpCode['code'] === 404) {
            view('errors/404');
        } else {
            view('errors/common', compact('contentMsg'));
        }

        die(1);
    }
}

if (!function_exists('logger')) {
    /**
     * Log
     *
     * @param string $filename
     * @param string $msg
     * @param string $datetimeFormat
     */
    function logger($filename, $msg, $datetimeFormat = 'Y-m-d')
    {
        if ($datetimeFormat) {
            $filename .= date($datetimeFormat);
        }
        $filename = BASE_PATH . 'cache/log/' . $filename . '.log';
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename));
        }
        @file_put_contents($filename, '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\r\n", FILE_APPEND);
    }
}

if (!function_exists('dd')) {
    /**
     * development debug
     *
     * @param mixed $msg
     */
    function dd($msg)
    {
        var_dump($msg);

        die(1);
    }
}

if (!function_exists('httpCodeMessage')) {
    /**
     * Http Code Message
     *
     * @param int $code
     * @return array
     */
    function httpCodeMessage($code)
    {
        switch ($code) {
            case 300:
                $msg = 'HTTP/1.1 300 Multiple Choices.';
                break;
            case 301:
                $msg = 'HTTP/1.1 301 Moved Permanently.';
                break;
            case 302:
                $msg = 'HTTP/1.1 302 Found.';
                break;
            case 303:
                $msg = 'HTTP/1.1 303 See Other.';
                break;
            case 304:
                $msg = 'HTTP/1.1 304 Not Modified.';
                break;
            case 305:
                $msg = 'HTTP/1.1 305 Use Proxy.';
                break;
            case 306:
                $msg = 'HTTP/1.1 306 Unused.';
                break;
            case 307:
                $msg = 'HTTP/1.1 307 Temporary Redirect.';
                break;
            case 400:
                $msg = 'HTTP/1.1 400 Bad Request.';
                break;
            case 401:
                $msg = 'HTTP/1.1 401 Unauthorized';
                break;
            case 403:
                $msg = 'HTTP/1.1 403 Forbidden.';
                break;
            case 404:
                $msg = 'HTTP/1.1 404 Not Found.';
                break;
            case 405:
                $msg = 'HTTP/1.1 405 Method Not Allowed.';
                break;
            case 408:
                $msg = 'HTTP/1.1 408 Request Timeout.';
                break;
            case 409:
                $msg = 'HTTP/1.1 409 Conflict.';
                break;
            case 500:
                $msg = 'HTTP/1.1 500 Internal Server Error.';
                break;
            case 502:
                $msg = 'HTTP/1.1 502 Bad Gateway.';
                break;
            case 503:
                $msg = 'HTTP/1.1 503 Service Unavailable.';
                break;
            case 504:
                $msg = 'HTTP/1.1 504 Gateway Timeout.';
                break;
            default:
                $code = 500;
                $msg = 'HTTP/1.1 500 Http Code Error.';
        }

        return ['code' => $code, 'msg' => $msg];
    }
}

if (!function_exists('view')) {
    /**
     * Output View
     *
     * @param $filename
     * @param array $compact
     */
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
            $file = BASE_PATH . 'app/views/' . $filename . '.php';
            if (file_exists($file)) {
                include_once $file;
            } else {
                showError(503, 'The specified view file does not exist.');
            }
        } else {
            showError(503, 'No view file specified.');
        }
        //unset parameter
        if (is_array($compact)) {
            foreach ($compact as $name => $value) {
                unset($GLOBALS[$$name]);
            }
        }
    }
}

if (!function_exists('baseUrl')) {
    function baseUrl()
    {
        $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
        $base_url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
        if (dirname($_SERVER['SCRIPT_NAME']) !== DIRECTORY_SEPARATOR) {
            $base_url .= isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : dirname(getenv('SCRIPT_NAME'));
        }
        return $base_url . '/';
    }
}



