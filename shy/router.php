<?php

/**
 * Shy Framework Router
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy;

class router
{
    /**
     * Controller
     *
     * @var string
     */
    public $controller = 'home';

    /**
     * Method of controller
     *
     * @var string
     */
    public $method = 'index';

    public function __construct()
    {
        if ($defaultController = config('default_controller')) {
            $this->controller = $defaultController;
        }
        $this->parseUrl();
        $this->runController();
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getMethod()
    {
        return $this->method;
    }

    private function parseUrl()
    {
        $pathString = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : getenv('REQUEST_URI');
        if (empty($pathString)) {
            showError(500, 'Url error.');
        }
        $path_param = explode('?', $pathString);
        if (empty($path_param[0])) {
            showError(500, 'Url error.');
        }
        $path = explode('/', $path_param[0]);
        if (!empty($path[1])) {
            $this->controller = lcfirst($path[1]);
            if (isset($path[2]) && !empty($path[2])) {
                $this->method = lcfirst($path[2]);
            }
        }
    }

    private function runController()
    {
        if (file_exists(BASE_PATH . 'app/controller/' . $this->controller . '.php')) {
            $class = 'app\controller\\' . $this->controller;
            if (class_exists($class)) {
                $controller = new $class;
                if (method_exists($controller, $this->method)) {
                    $method = $this->method;
                    $controller->$method();

                    if (config('slow_log')) {
                        global $startTime;
                        $difference = time() - $startTime;
                        if ($difference > config('slow_log_limit')) {
                            logger('slowLog/log', json_encode(['controller' => $this->controller, 'method' => $this->method, 'difference' => $difference]));
                        }
                    }

                    exit(0);
                }
            }
        }

        showError(404);
    }

}