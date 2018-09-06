<?php

/**
 * Shy Framework Router
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http;

use shy\http\exception\httpException;

class router
{
    /**
     * Controller
     *
     * @var string
     */
    private $controller = 'home';

    /**
     * Method of controller
     *
     * @var string
     */
    private $method = 'index';

    /**
     * Is Parse Route Controller And Method Success
     *
     * @var bool
     */
    private $success = false;

    /**
     * Base Url
     *
     * @var string
     */
    private $baseUrl = '';

    /**
     * Middleware
     *
     * @var array
     */
    private $middleware = [];

    /**
     * Get Controller Name
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get Method Name
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Pipeline Call Method
     *
     * @param request $request
     * @param $next
     */
    public function handle(request $request, $next)
    {
        $this->baseUrl = $request->getBaseUrl();
        if (config('route_by_config')) {
            $this->success = $this->configRoute();
        }
        if (!$this->success && config('route_by_path')) {
            $this->success = $this->pathRoute();
        }
        if (!$this->success) {
            throw new httpException(404);
        }

        if (empty($this->middleware)) {
            $next($this->runMethod());
        } else {
            foreach ($this->middleware as $key => $middleware) {
                $middleware = 'app\\middleware\\' . $middleware;
                if (class_exists($middleware)) {
                    $this->middleware[$key] = $middleware;
                }
            }
            shy('pipeline')
                ->send($request)
                ->through($this->middleware)
                ->then(function () use ($next) {
                    $next($this->runMethod());
                });
        }

        /**
         * slow_log
         */
        if (config('slow_log')) {
            $difference = microtime(true) - SHY_START;
            if ($difference > config('slow_log_limit')) {
                logger('slowLog/log', json_encode(['controller' => $this->controller, 'method' => $this->method, 'difference' => $difference]));
            }
        }
    }

    /**
     * Route By Config
     *
     * @return bool
     */
    private function configRoute()
    {
        $fasterRouter = [];
        $router = config_all('router');
        if (isset($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $fasterRouter[$path] = ['handle' => $handle];
            }
        }
        if (isset($router['group'])) {
            foreach ($router['group'] as $group) {
                if (isset($group['path']) && is_array($group['path'])) {
                    $groupPath = $group['path'];
                    unset($group['path']);
                    $prefix = '';
                    if (isset($group['prefix']) && !empty($group['prefix'])) {
                        $prefix = '/' . $group['prefix'];
                    }
                    foreach ($groupPath as $path => $handle) {
                        $fasterRouter[$prefix . $path] = array_merge($group, ['handle' => $handle]);
                    }
                }
            }
        }
        //dd(isset($fasterRouter[$this->baseUrl]));

        if (isset($fasterRouter[$this->baseUrl])) {
            $handle = $fasterRouter[$this->baseUrl]['handle'];
            if (isset($fasterRouter[$this->baseUrl]['middleware'])) {
                $this->middleware = $fasterRouter[$this->baseUrl]['middleware'];
            }

            list($this->controller, $this->method) = explode('@', $handle);

            return true;
        }

        //$fasterRouterJson = json_encode($fasterRouter);
        //$signature = md5($fasterRouterJson);
    }

    /**
     * Route By Base Url
     *
     * @return bool
     */
    private function pathRoute()
    {
        $path = explode('/', $this->baseUrl);
        if (!empty($path[1])) {
            $this->controller = lcfirst($path[1]);
            if (isset($path[2]) && !empty($path[2])) {
                $this->method = lcfirst($path[2]);

                return true;
            }
        } elseif (count($path) === 2) {
            //home page
            if ($defaultController = config('default_controller')) {
                $this->controller = $defaultController;
            }

            return true;
        }
    }

    /**
     * Run Method
     *
     * @return mixed
     */
    private function runMethod()
    {
        $class = 'app\\controller\\' . $this->controller;
        if (class_exists($class)) {
            $controller = new $class;
            if (method_exists($controller, $this->method)) {
                $method = $this->method;
                return $controller->$method();
            }
        }

        throw new httpException(404);
    }

}
