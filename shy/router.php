<?php

/**
 * Shy Framework Router
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy;

use config\app;

class router
{
    public $controller = app::DEFAULT_CONTROLLER;
    public $method = 'index';

    public function __construct()
    {
        $this->parseUrl();
        $this->runController();
    }

    //url带.php的情况没有考虑
    private function parseUrl()
    {
        $complete_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
        $complete_url .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
        $complete_url .= isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
        if (!filter_var($complete_url, FILTER_VALIDATE_URL)) {
            header('HTTP/1.1 400 Bad Request.', TRUE, 400);
            echo 'url invalid.';
            exit(1); // EXIT_ERROR
        }
        $rewrite_path = substr($complete_url, strlen(BASE_URL));
        if (!empty($rewrite_path)) {
            $path_param = explode('?', $rewrite_path);
            if (!empty($path_param[0])) {
                $path = explode('/', $path_param[0]);
                if (!empty($path[0])) {
                    $this->controller = lcfirst($path[0]);
                    if (isset($path[1]) && !empty($path[1])) {
                        $this->method = lcfirst($path[1]);
                    }
                }
            }
        }
    }

    private function runController()
    {
        if (file_exists(BASE_PATH . '/../app/controller/' . $this->controller . '.php')) {
            $class = '\controller\\' . $this->controller;
            if (class_exists($class)) {
                $controller = new $class;
                if (method_exists($controller, $this->method)) {
                    $method = $this->method;
                    $controller->$method();
                } else {
                    header('HTTP/1.1 404 Not Found.', TRUE, 404);
                    echo 'Method not found.';
                    exit(1); // EXIT_ERROR
                }
            } else {
                header('HTTP/1.1 404 Not Found.', TRUE, 404);
                echo 'Controller Class not found.';
                exit(1); // EXIT_ERROR
            }
        } else {
            header('HTTP/1.1 404 Not Found.', TRUE, 404);
            echo 'Controller file not found.';
            exit(1); // EXIT_ERROR
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getMethod()
    {
        return $this->method;
    }
}