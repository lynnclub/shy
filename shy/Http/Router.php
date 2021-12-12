<?php

namespace Shy\Http;

use Shy\Core\Contract\Pipeline;
use Shy\Core\Facade\Hook;
use Shy\Http\Contract\Router as RouterContract;
use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\Exception\HttpException;

class Router implements RouterContract
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $controller;

    /**
     * @var string
     */
    protected $method = 'index';

    /**
     * @var array
     */
    protected $pathParam = [];

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
    protected $routeConfig;

    /**
     * @var array
     */
    protected $routeIndex;

    /**
     * @var array
     */
    protected $middleware;

    /**
     * Is parse route success
     *
     * @var bool
     */
    protected $parseRouteSuccess;

    /**
     * Router constructor.
     *
     * @param string $defaultNamespace
     * @param string $defaultController
     */
    public function __construct($defaultNamespace = null, $defaultController = null)
    {
        $this->namespace = $defaultNamespace ?? 'App\\Http\\Controller\\';
        $this->controller = $defaultController ?? config('app.default_controller');
    }

    /**
     * Initialize for cycle
     */
    public function initialize()
    {
        $this->pathParam = [];
        $this->routeConfig = [];
        $this->routeIndex = [];
        $this->middleware = [];
        $this->parseRouteSuccess = FALSE;
    }

    /**
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
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
    public function getRouteConfig()
    {
        return $this->routeConfig;
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
     * @return array
     */
    public function getPathParam()
    {
        return $this->pathParam;
    }

    /**
     * Run
     *
     * @param RequestContract $request
     * @return mixed|View|string
     * @throws \Exception
     */
    public function run(RequestContract $request)
    {
        $this->initialize();

        $this->host = $request->getHttpHost();
        $this->pathInfo = trim($request->getPathInfo(), " \/\t\n\r\0\x0B");

        // Parse Route
        if (config('app.route_by_config')) {
            $this->parseRouteByConfig();
        }
        if (!$this->parseRouteSuccess && config('app.route_by_path')) {
            $this->parseRouteByPath();
        }

        // Check controller
        if (!$this->parseRouteSuccess) {
            throw new httpException(404, 'Route not found. ' . $this->pathInfo);
        }
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            throw new httpException(404, 'Controller not found. ' . $this->pathInfo . ' ' . $this->controller . '->' . $this->method);
        }

        // Hook
        Hook::run('run_controller_before', $this->pathParam);

        // Run controller and middleware
        if (empty($this->middleware)) {
            $response = $this->runController();
        } else {
            $response = shy(Pipeline::class)
                ->send(...$this->pathParam)
                ->through($this->middleware)
                ->then(function () {
                    return $this->runController();
                });

            shy()->remove(array_values($this->middleware));
        }

        shy()->remove($this->controller);

        return $response;
    }

    /**
     * Parse route by config
     */
    protected function parseRouteByConfig()
    {
        // Read from cache, or build
        if (empty($this->routeConfig)) {
            $isCacheOn = config('app.cache');
            if ($isCacheOn && config()->has('__ROUTE_CONFIG')) {
                $this->routeConfig = config('__ROUTE_CONFIG');
                $this->routeIndex = config('__ROUTE_INDEX');
            } else {
                $this->buildRouteByConfig();

                if ($isCacheOn) {
                    config()->set('__ROUTE_CONFIG', $this->routeConfig);
                    config()->set('__ROUTE_INDEX', $this->routeIndex);
                }
            }
        }

        // Parse route with host
        if (isset($this->routeConfig[$this->host])) {
            $this->doParseRouteByConfig($this->routeConfig[$this->host], $this->routeIndex[$this->host]);
        }
        // Parse route without host
        if (!$this->parseRouteSuccess && isset($this->routeConfig[''])) {
            $this->doParseRouteByConfig($this->routeConfig[''], $this->routeIndex['']);
        }
    }

    /**
     * @param $routeConfig
     * @param $routeIndex
     */
    protected function doParseRouteByConfig($routeConfig, $routeIndex)
    {
        if (isset($routeConfig[$this->pathInfo])) {
            $this->useParsedRouteByConfig($routeConfig[$this->pathInfo]);
        } else {
            $pathArray = explode('/', $this->pathInfo);
            $count = count($pathArray);

            if (isset($routeIndex[$count])) {
                $routeIndex = $routeIndex[$count];
                $matchSuccess = true;
                $matchPath = [];
                $matchParam = [];
                foreach ($pathArray as $path) {
                    if (isset($routeIndex[$path])) {
                        $matchPath[] = $path;
                        $routeIndex = $routeIndex[$path];
                    } elseif (isset($routeIndex['?'])) {
                        $matchPath[] = '?';
                        $matchParam[] = $path;
                        $routeIndex = $routeIndex['?'];
                    } else {
                        $matchSuccess = false;
                        break;
                    }
                }

                $matchPath = implode('/', $matchPath);
                if ($matchSuccess && empty($routeIndex) && isset($routeConfig[$matchPath])) {
                    $this->useParsedRouteByConfig($routeConfig[$matchPath], $matchParam);
                }
            }
        }
    }

    /**
     * @param $config
     * @param array $pathParam
     */
    protected function useParsedRouteByConfig($config, $pathParam = [])
    {
        list($this->controller, $this->method) = array_pad(explode('@', $config['hdl']), 2, 'index');

        $this->middleware = $config['mdw'] ?? [];
        $this->pathParam = $pathParam;

        $this->parseRouteSuccess = TRUE;
    }

    /**
     * Build route by config
     */
    public function buildRouteByConfig()
    {
        $this->routeConfig = [];
        $router = config('router');

        // Path route
        if (isset($router['path']) && is_array($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $this->doBuildRouteByConfig('', $path, $this->namespace, $handle);
            }
        }

        // Group route
        if (isset($router['group']) && is_array($router['group'])) {
            foreach ($router['group'] as $group) {
                if (!isset($group['path']) || !is_array($group['path'])) {
                    continue;
                }

                $host = '';
                if (isset($group['host']) && (is_string($group['host']) || is_array($group['host']))) {
                    $host = $group['host'];
                }

                $prefix = '';
                if (isset($group['prefix']) && is_string($group['prefix'])) {
                    $prefix = trim($group['prefix'], " \/\t\n\r\0\x0B") . '/';
                }

                $namespace = $this->namespace;
                if (isset($group['namespace']) && is_string($group['namespace']) && !empty($group['namespace'])) {
                    $namespace = trim($group['namespace'], " \\\/\t\n\r\0\x0B") . '\\';
                }

                $middleware = [];
                if (isset($group['middleware']) && is_array($group['middleware'])) {
                    $middleware = $this->getMiddlewareClassInConfig($group['middleware']);
                }

                foreach ($group['path'] as $path => $handle) {
                    $path = $prefix . trim($path, " \/\t\n\r\0\x0B");

                    $this->doBuildRouteByConfig($host, $path, $namespace, $handle, $middleware);
                }
            }
        }
    }

    /**
     * Do build route by config
     *
     * @param string|array $host
     * @param string $path
     * @param string $namespace
     * @param string $handle
     * @param array $middleware
     */
    protected function doBuildRouteByConfig($host, string $path, string $namespace, string $handle, array $middleware = [])
    {
        $path = trim($path, " \/\t\n\r\0\x0B");
        $handle = trim($handle, " \\\/\t\n\r\0\x0B");

        $routeConfig = [
            'hdl' => $namespace . ucfirst($handle),
        ];

        if (!empty($middleware)) {
            $routeConfig['mdw'] = $middleware;
        }

        $this->doBuildHostRouteByConfig($host, $path, $routeConfig);
        $this->doBuildHostRouteIndexByConfig($host, $path);
    }

    /**
     * @param $host
     * @param string $path
     * @param array $routeConfig
     */
    protected function doBuildHostRouteByConfig($host, string $path, array $routeConfig)
    {
        if (empty($host)) {
            $this->routeConfig[''][$path] = $routeConfig;
        } else {
            if (is_array($host)) {
                foreach ($host as $oneHost) {
                    $this->doBuildHostRouteByConfig($oneHost, $path, $routeConfig);
                }
            } elseif (is_string($host) && preg_match("/^(http:\/\/|https:\/\/)?([^\/]+)/i", $host, $matches) && !empty($matches[2])) {
                $this->routeConfig[$matches[2]][$path] = $routeConfig;
            }
        }
    }

    /**
     * @param $host
     * @param string $path
     */
    protected function doBuildHostRouteIndexByConfig($host, string $path)
    {
        if (is_array($host)) {
            foreach ($host as $oneHost) {
                $this->doBuildHostRouteIndexByConfig($oneHost, $path);
            }
        } elseif (empty($host) || (preg_match("/^(http:\/\/|https:\/\/)?([^\/]+)/i", $host, $matches) && !empty($matches[2]))) {
            if (!empty($matches[2])) {
                $host = $matches[2];
            }

            $paths = explode('/', $path);
            $count = count($paths);
            if (!isset($this->routeIndex[$host][$count])) {
                $this->routeIndex[$host][$count] = 1;
            }

            $currentIndex = &$this->routeIndex[$host][$count];
            foreach ($paths as $currentPath) {
                if (!isset($currentIndex[$currentPath])) {
                    $currentIndex[$currentPath] = 1;
                }

                $currentIndex = &$currentIndex[$currentPath];
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
                // Full class name
                $middleware[] = $name;
            }
        }

        return array_filter($middleware);
    }

    /**
     * Parse route by path
     */
    protected function parseRouteByPath()
    {
        $path = explode('/', $this->pathInfo);
        if (!empty($path[0])) {
            $this->controller = ucfirst($path[0]);
            if (!empty($path[1])) {
                $this->method = ucfirst($path[1]);
                if (isset($path[2])) {
                    $this->pathParam = array_slice($path, 2);
                }
            }
        }

        $this->parseRouteSuccess = TRUE;
    }

    /**
     * Run controller
     *
     * @return mixed
     */
    protected function runController()
    {
        return shy(Pipeline::class)
            ->send(...$this->pathParam)
            ->through($this->controller)
            ->via($this->method)
            ->run();
    }
}
