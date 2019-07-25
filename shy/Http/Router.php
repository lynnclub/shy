<?php

namespace Shy\Http;

use Shy\Http\Contracts\Router as RouterContract;
use Shy\Http\Contracts\Request as RequestContract;
use Shy\Core\Contracts\Pipeline;
use Shy\Http\Exceptions\HttpException;
use RuntimeException;

class Router implements RouterContract
{
    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var array
     */
    protected $middleware;

    /**
     * Is Parse Route Success
     *
     * @var bool
     */
    protected $parseRouteSuccess;

    /**
     * Initialize in cycle
     */
    protected function initialize()
    {
        $this->parseRouteSuccess = false;
        $this->controller = config_key('default_controller');
        $this->method = 'index';
        $this->pathInfo = '/';
        $this->middleware = [];
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Pipeline Handle
     *
     * @param \Closure $next
     * @param RequestContract $request
     * @return string|view
     */
    public function handle($next, RequestContract $request)
    {
        $this->initialize();

        $this->pathInfo = $request->getPathInfo();
        if (!is_string($this->pathInfo)) {
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
        if (!$this->parseRouteSuccess) {
            throw new httpException(404, 'Route not found 404');
        }
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            throw new httpException(404, 'Route not found 404');
        }


        /**
         * Run controller and middleware
         */
        if (empty($this->middleware)) {
            $response = $this->runController();
        } else {
            $response = shy(Pipeline::class)
                ->through($this->middleware)
                ->then(function () {
                    return $this->runController();
                });
            //shy_clear(array_values($this->middleware));
        }
        //shy_clear($this->controller);

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
        if (config_key('debug') || empty($routerIndex) || !is_array($routerIndex)) {
            $routerIndex = $this->buildRouterIndex();
        }
        /**
         * parse router
         */
        if (isset($routerIndex[$this->pathInfo])) {
            if (isset($routerIndex[$this->pathInfo]['middleware'])) {
                $this->middleware = $routerIndex[$this->pathInfo]['middleware'];
            }

            $handle = $routerIndex[$this->pathInfo]['handle'];
            list($this->controller, $this->method) = explode('@', $handle);

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
        $controllerNamespace = 'App\\Http\\Controllers\\';
        /**
         * path
         */
        if (isset($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $routerIndex[$path] = ['handle' => $controllerNamespace . ucfirst($handle)];
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
                     * namespace
                     */
                    if (isset($oneGroup['namespace']) && is_string($oneGroup['namespace']) && !empty($oneGroup['namespace'])) {
                        $controllerNamespace = $oneGroup['namespace'] . '\\';
                    }
                    /**
                     * middleware
                     */
                    $middleware = [];
                    if (isset($oneGroup['middleware']) && is_array($oneGroup['middleware'])) {
                        $middleware = $this->getMiddlewareClassByConfig($oneGroup['middleware']);
                    }
                    foreach ($oneGroup['path'] as $path => $handle) {
                        $routerIndex[$prefix . $path] = ['handle' => $controllerNamespace . ucfirst($handle), 'middleware' => $middleware];
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

        $path = explode('/', $this->pathInfo);
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
        return shy(Pipeline::class)
            ->through($this->controller)
            ->via($this->method)
            ->run();
    }

}
