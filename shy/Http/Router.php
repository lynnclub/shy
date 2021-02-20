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
    protected $method = 'index';

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
    protected $host;

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

    public function __construct($controllerNamespace = 'App\\Http\\Controllers\\', $defaultController = null)
    {
        $this->controllerNamespace = $controllerNamespace;
        $this->controller = $defaultController ?? config('app.default_controller');
    }

    /**
     * Initialize in cycle
     */
    public function initialize()
    {
        $this->parseRouteSuccess = FALSE;
        $this->routeIndex = [];
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
     * @return mixed|View|string
     * @throws \Exception
     */
    public function handle($next, RequestContract $request)
    {
        $this->initialize();

        $this->host = $request->getHttpHost();
        $this->pathInfo = trim($request->getPathInfo(), " \/\t\n\r\0\x0B");

        /**
         * Parse Route
         */
        if (config('app.route_by_config')) {
            $this->parseRouteByConfig();
        }
        if (!$this->parseRouteSuccess && config('app.route_by_path')) {
            $this->parseRouteByPath();
        }

        /**
         * Check controller
         */
        if (!$this->parseRouteSuccess) {
            throw new httpException(404, 'Route not found. ' . $this->pathInfo);
        }
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            throw new httpException(404, 'Controller not found. ' . $this->pathInfo . ' ' . $this->controller . '->' . $this->method);
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
        if (isset($this->routeIndex[$this->host])) {
            $this->doParseRouteByConfig($this->routeIndex[$this->host]);
        }
        if (!$this->parseRouteSuccess && isset($this->routeIndex[''])) {
            $this->doParseRouteByConfig($this->routeIndex['']);
        }
    }

    protected function doParseRouteByConfig($routeIndex)
    {
        if (isset($routeIndex[$this->pathInfo])) {
            $this->writeParseRouteByConfig($routeIndex[$this->pathInfo]);
        } else {
            $paramStart = strrpos($this->pathInfo, '/');

            $pathInfo = '';
            $param = $this->pathInfo;
            if ($paramStart > 0) {
                $pathInfo = substr($this->pathInfo, 0, $paramStart);
                $param = substr($this->pathInfo, $paramStart + 1);
            }

            if (isset($routeIndex[$pathInfo]) && isset($routeIndex[$pathInfo]['with_param'])) {
                $this->writeParseRouteByConfig($routeIndex[$pathInfo], $param);
            } elseif ($paramStart = strrpos($pathInfo, '/')) {
                $pathInfo = substr($this->pathInfo, 0, $paramStart);
                $param = substr($this->pathInfo, $paramStart + 1);

                if (isset($routeIndex[$pathInfo]) && isset($routeIndex[$pathInfo]['with_param'])) {
                    $this->writeParseRouteByConfig($routeIndex[$pathInfo], $param);
                }
            }
        }
    }

    protected function writeParseRouteByConfig($config, $param = null)
    {
        list($this->controller, $this->method) = array_pad(explode('@', $config['handle']), 2, 'index');

        if (isset($config['middleware'])) {
            $this->middleware = array_filter($config['middleware']);
        }

        $this->param = $param;

        $this->parseRouteSuccess = TRUE;
    }

    /**
     * Build route index by config
     */
    public function buildRouteIndexByConfig()
    {
        $this->routeIndex = [];
        $router = config('router');

        /**
         * Build path index
         */
        if (isset($router['path']) && is_array($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $this->addRouteIndexByConfig($path, $handle, $this->controllerNamespace);
            }
        }

        /**
         * Build group path index
         */
        if (isset($router['group']) && is_array($router['group'])) {
            foreach ($router['group'] as $group) {
                if (isset($group['path']) && is_array($group['path'])) {
                    $host = '';
                    if (isset($group['host']) && (is_string($group['host']) || is_array($group['host']))) {
                        $host = $group['host'];
                    }

                    $prefix = '';
                    if (isset($group['prefix']) && is_string($group['prefix'])) {
                        $prefix = trim($group['prefix'], " \/\t\n\r\0\x0B");
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
                        $this->addRouteIndexByConfig($prefix . '/' . trim($path, " \/\t\n\r\0\x0B"), $handle, $controllerNamespace, $middlewareClass, $host);
                    }
                }
            }
        }
    }

    /**
     * Add route index by config
     *
     * @param string $path
     * @param string $handle
     * @param string $controllerNamespace
     * @param array $middlewareClass
     * @param string|array $host
     */
    protected function addRouteIndexByConfig($path, $handle, string $controllerNamespace, array $middlewareClass = [], $host = '')
    {
        $path = trim($path, " \/\t\n\r\0\x0B");
        $handle = trim($handle, " \\\/\t\n\r\0\x0B");

        if (is_string($path) && is_string($handle)) {
            $index = [];
            if (substr($path, 0) === '?') {
                $path = '';
                $index['with_param'] = 0;
            } elseif (substr($path, -2) === '/?') {
                $path = substr($path, 0, -2);
                $index['with_param'] = 1;

                if (substr($path, -2) === '/?') {
                    unset($this->routeIndex[$path]);
                    $path = substr($path, 0, -2);
                    $index['with_param'] = 2;
                }
            }

            $index['handle'] = $controllerNamespace . ucfirst($handle);

            if (!empty($middlewareClass)) {
                $index['middleware'] = $middlewareClass;
            }

            if (empty($host)) {
                $this->routeIndex[''][$path] = $index;
            }

            $this->addHostRouteIndexByConfig($host, $path, $index);
        }
    }

    protected function addHostRouteIndexByConfig($host, $path, $index)
    {
        if (is_array($host)) {
            foreach ($host as $item) {
                $this->addHostRouteIndexByConfig($item, $path, $index);
            }
        } elseif (is_string($host) && preg_match("/^(http:\/\/|https:\/\/)?([^\/]+)/i", $host, $matches) && !empty($matches[2])) {
            $this->routeIndex[$matches[2]][$path] = $index;
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
                // Full classname
                $middleware[] = $name;
            }
        }

        return $middleware;
    }

    /**
     * Parse route by path
     */
    protected function parseRouteByPath()
    {
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

            $this->parseRouteSuccess = TRUE;
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
