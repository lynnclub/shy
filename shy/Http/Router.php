<?php

namespace Shy\Http;

use Shy\Http\Contracts\Router as RouterContract;
use Shy\Http\Contracts\Request as RequestContract;
use Shy\Core\Contracts\Pipeline;
use Shy\Http\Exceptions\HttpException;

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
    protected $param;

    /**
     * @var string
     */
    protected $controllerNamespace;

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var array
     */
    protected $routeIndex;

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
        $this->controller = config('app.default_controller');
        $this->method = 'index';
        $this->routeIndex = [];
        $this->controllerNamespace = 'App\\Http\\Controllers\\';
        $this->pathInfo = '';
        $this->param = null;
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
    public function getRouteIndex()
    {
        return $this->routeIndex;
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
            throw new httpException(500, 'Route invalid.');
        }
        $this->pathInfo = trim($this->pathInfo, " \/\t\n\r\0\x0B");

        /**
         * Parse Route
         */
        if (config('app.route_by_config')) {
            $this->parseRouteByConfig();
        }
        if (config('app.route_by_path')) {
            $this->parseRouteByPath();
        }

        /**
         * Check controller
         */
        if (!$this->parseRouteSuccess) {
            throw new httpException(404, 'Route not found 404.');
        }
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            throw new httpException(404, 'Route controller not found 404.');
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
            shy()->remove(array_values($this->middleware));
        }
        shy()->remove($this->controller);

        return $next($response);
    }

    /**
     * Parse route by config
     */
    protected function parseRouteByConfig()
    {
        /**
         * Read cache or build route index
         */
        $isCache = config('app.cache');
        if ($isCache && config()->has('__ROUTE_INDEX')) {
            $this->routeIndex = config('__ROUTE_INDEX');
        }
        if (empty($this->routeIndex) || !is_array($this->routeIndex)) {
            $this->buildRouteIndexByConfig();

            if ($isCache) {
                config()->set('__ROUTE_INDEX', $this->routeIndex);
            }
        }
        /**
         * Parse route
         */
        if (isset($this->routeIndex[$this->pathInfo])) {
            $this->writeParseRouteByConfig($this->routeIndex[$this->pathInfo]);
        } elseif ($paramStart = strrpos($this->pathInfo, '/')) {
            $pathInfo = substr($this->pathInfo, 0, $paramStart);
            $param = substr($this->pathInfo, $paramStart + 1);

            if (isset($this->routeIndex[$pathInfo]) && isset($this->routeIndex[$pathInfo]['with_param'])) {
                $this->writeParseRouteByConfig($this->routeIndex[$pathInfo], $param);
            }
        }
    }

    protected function writeParseRouteByConfig($config, $param = null)
    {
        list($this->controller, $this->method) = array_pad(explode('@', $config['handle']), 2, 'index');

        if (isset($config['middleware'])) {
            $this->middleware = $config['middleware'];
        }

        $this->param = $param;

        $this->parseRouteSuccess = true;
    }

    /**
     * Build route index by config
     */
    protected function buildRouteIndexByConfig()
    {
        $this->routeIndex = [];
        $router = config('router');

        /**
         * Build path index
         */
        if (isset($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $this->addRouteIndexByConfig($path, $handle, $this->controllerNamespace);
            }
        }

        /**
         * Build group path index
         */
        if (isset($router['group'])) {
            foreach ($router['group'] as $group) {
                if (isset($group['path']) && is_array($group['path'])) {
                    $prefix = '';
                    if (isset($group['prefix']) && is_string($group['prefix']) && !empty($group['prefix'])) {
                        $prefix = $group['prefix'];
                    }

                    $controllerNamespace = $this->controllerNamespace;
                    if (isset($group['namespace']) && is_string($group['namespace']) && !empty($group['namespace'])) {
                        $controllerNamespace = trim($group['namespace'], " \\\/\t\n\r\0\x0B") . '\\';
                    }

                    $middlewareClass = [];
                    if (isset($group['middleware']) && is_array($group['middleware'])) {
                        $middlewareClass = $this->getMiddlewareClassInConfig($group['middleware']);
                    }

                    foreach ($group['path'] as $path => $handle) {
                        $this->addRouteIndexByConfig($prefix . $path, $handle, $controllerNamespace, $middlewareClass);
                    }
                }
            }
        }
    }

    /**
     * Add route index by config
     *
     * @param $path
     * @param $handle
     * @param $controllerNamespace
     * @param array $middlewareClass
     */
    protected function addRouteIndexByConfig($path, $handle, $controllerNamespace, $middlewareClass = [])
    {
        if (is_string($handle)) {
            $path = trim($path, " \/\t\n\r\0\x0B");

            if (substr($path, -2) === '/?') {
                $path = substr($path, 0, -2);
                $this->routeIndex[$path]['with_param'] = true;
            }

            $this->routeIndex[$path]['handle'] = $controllerNamespace . ucfirst(trim($handle, " \\\/\t\n\r\0\x0B"));

            if (!empty($middlewareClass)) {
                $this->routeIndex[$path]['middleware'] = $middlewareClass;
            }
        }
    }

    /**
     * Get middleware class in config
     *
     * @param $middlewareNames
     * @return array
     */
    protected function getMiddlewareClassInConfig(array $middlewareNames)
    {
        $middleware = [];
        if (empty($middlewareNames)) {
            return $middleware;
        }

        $config = config('middleware');
        foreach ($middlewareNames as $middlewareName) {
            list($name, $param) = array_pad(explode(':', $middlewareName, 2), 2, null);

            if (isset($config[$name])) {
                $className = $config[$name];
                if (is_array($className)) {
                    $middleware = array_merge($middleware, $className);
                } else {
                    $middleware[] = $className . (isset($param) ? ':' . $param : '');
                }
            } else {
                //This step does not report an error
                $middleware[] = $name;
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
        if (isset($path[0])) {
            if (!empty($path[0])) {
                $this->controller = ucfirst($path[0]);
                if (isset($path[1]) && !empty($path[1])) {
                    $this->method = ucfirst($path[1]);
                    if (isset($path[2])) {
                        $this->param = $path[2];
                    }
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
        $pipeline = shy(Pipeline::class)
            ->through($this->controller)
            ->via($this->method);

        if (isset($this->param)) {
            $pipeline->send($this->param);
        }

        return $pipeline->run();
    }

}
