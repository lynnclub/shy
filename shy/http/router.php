<?php
/**
 * Shy Framework Router
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http;

use shy\core\pipeline;
use shy\http\exception\httpException;
use RuntimeException;

class router
{
    /**
     * Controller
     *
     * @var string
     */
    protected $controller;

    /**
     * Method of Controller
     *
     * @var string
     */
    protected $method;

    /**
     * Is Parse Route Success
     *
     * @var bool
     */
    protected $parseRouteSuccess;

    /**
     * Base Url Path
     *
     * @var string
     */
    protected $uri;

    /**
     * Middleware
     *
     * @var array
     */
    protected $middleware;

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
     * Get Middleware Name
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Init in cycle
     */
    protected function init()
    {
        $this->parseRouteSuccess = false;
        $this->controller = config_key('default_controller');
        $this->method = 'index';
        $this->middleware = [];
    }

    /**
     * Pipeline Handle
     *
     * @param \Closure $next
     * @param \shy\http\request $request
     * @return string|view
     */
    public function handle($next, $request)
    {
        $this->init();

        $this->uri = $request->getUri();
        if (!is_string($this->uri)) {
            throw new httpException(404, 'Route not found 404');
        }

        /**
         * Parse Router
         */
        if (config_key('route_by_config')) {
            $this->parseRouteByConfig();
        }
        if (config_key('route_by_path')) {
            $this->parseRouteByPath();
        }
        /**
         * Check controller
         */
        if ($this->parseRouteSuccess) {
            $this->controller = 'app\\http\\controller\\' . $this->controller;
            if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
                throw new httpException(404, 'Route not found 404');
            }
        } else {
            throw new httpException(404, 'Route not found 404');
        }
        /**
         * Run controller and middleware
         */
        if (empty($this->middleware)) {
            $response = $this->runController();
        } else {
            $response = shy(pipeline::class)
                ->through($this->middleware)
                ->then(function () {
                    return $this->runController();
                });
            shy_clear(array_values($this->middleware));
        }
        shy_clear($this->controller);

        return $next($response);
    }

    /**
     * Parse route by config
     */
    protected function parseRouteByConfig()
    {
        $routerIndexCache = @file_get_contents(CACHE_PATH . 'app/router.cache');
        $routerIndex = json_decode($routerIndexCache, true);
        /**
         * Read or build router index
         */
        if (config_key('env') !== 'production' || empty($routerIndex) || !is_array($routerIndex)) {
            $routerIndex = $this->buildRouterIndex();
        }
        /**
         * parse router
         */
        if (isset($routerIndex[$this->uri])) {
            $handle = $routerIndex[$this->uri]['handle'];
            list($this->controller, $this->method) = explode('@', $handle);
            /**
             * Middleware
             */
            if (isset($routerIndex[$this->uri]['middleware'])) {
                $this->middleware = $this->getMiddlewareClassByConfig($routerIndex[$this->uri]['middleware']);
            }

            $this->parseRouteSuccess = true;
        }
    }

    /**
     * Build router index
     */
    protected function buildRouterIndex()
    {
        $router = config('router');
        $routerIndex = [];
        /**
         * path
         */
        if (isset($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $routerIndex[$path] = ['handle' => $handle];
            }
        }
        /**
         * group
         */
        if (isset($router['group'])) {
            foreach ($router['group'] as $oneGroup) {
                if (isset($oneGroup['path']) && is_array($oneGroup['path'])) {
                    /**
                     * prefix
                     */
                    $prefix = '';
                    if (isset($oneGroup['prefix']) && is_string($oneGroup['prefix']) && !empty($oneGroup['prefix'])) {
                        $prefix = '/' . $oneGroup['prefix'];
                    }
                    /**
                     * middleware
                     */
                    $middleware = [];
                    if (isset($oneGroup['middleware']) && is_array($oneGroup['middleware'])) {
                        $middleware = $oneGroup['middleware'];
                    }
                    foreach ($oneGroup['path'] as $path => $handle) {
                        $routerIndex[$prefix . $path] = ['handle' => $handle, 'middleware' => $middleware];
                    }
                }
            }
        }

        file_put_contents(CACHE_PATH . 'app/router.cache', json_encode($routerIndex));

        return $routerIndex;
    }

    /**
     * Get middleware class by config
     *
     * @param $middlewareNames
     * @return array
     */
    protected function getMiddlewareClassByConfig(array $middlewareNames)
    {
        $middleware = [];
        if (!empty($middlewareNames)) {
            $middlewareConfig = config('middleware');
            foreach ($middlewareNames as $middlewareName) {
                if (isset($middlewareConfig[$middlewareName])) {
                    $middlewareClass = $middlewareConfig[$middlewareName];
                    if (is_string($middlewareClass)) {
                        $middleware[] = $middlewareClass;
                    } elseif (is_array($middlewareClass)) {
                        $middleware = array_merge($middleware, $middlewareClass);
                    } else {
                        throw new RuntimeException('Middleware name ' . $middlewareName . ' config error.');
                    }
                } else {
                    throw new RuntimeException('Middleware name ' . $middlewareName . ' config not found.');
                }
            }
        }

        return $middleware;
    }

    /**
     * Parse route by path
     *
     * @return bool
     */
    protected function parseRouteByPath()
    {
        if ($this->parseRouteSuccess) {
            return false;
        }

        $path = explode('/', $this->uri);
        if (isset($path[1])) {
            if (!empty($path[1])) {
                $this->controller = lcfirst($path[1]);
                if (isset($path[2]) && !empty($path[2])) {
                    $this->method = lcfirst($path[2]);
                }
            }

            $this->parseRouteSuccess = true;
        }
    }

    /**
     * Run controller
     *
     * @return mixed
     */
    protected function runController()
    {
        return shy(pipeline::class)
            ->through($this->controller)
            ->via($this->method)
            ->run();
    }

}
