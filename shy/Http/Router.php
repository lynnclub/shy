<?php

namespace Shy\Http;

use Shy\Contract\Pipeline;
use Shy\Facade\Hook;
use Shy\Http\Contract\Router as RouterContract;
use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\Exception\HttpException;

class Router implements RouterContract
{
    /**
     * 控制器默认命名空间
     * Default namespace of controller
     *
     * @var string
     */
    protected $defaultNamespace;

    /**
     * 被调用控制器
     * Called controller
     *
     * @var string
     */
    protected $controller;

    /**
     * 被调用控制器方法
     * Called controller method
     *
     * @var string
     */
    protected $method;

    /**
     * 路径域名
     *
     * @var string
     */
    protected $host;

    /**
     * 请求路径
     *
     * @var string
     */
    protected $pathInfo;

    /**
     * 路由配置
     *
     * @var array
     */
    protected $routeConfig;

    /**
     * 路由索引
     *
     * @var array
     */
    protected $routeIndex;

    /**
     * 路径参数
     *
     * @var array
     */
    protected $pathParam = [];

    /**
     * 中间件
     *
     * @var array
     */
    protected $middleware;

    /**
     * 是否解析成功
     * Is the parse successful
     *
     * @var bool
     */
    protected $parseSuccess;

    /**
     * 初始化，以便循环复用
     * Initialize in loop
     */
    public function initialize()
    {
        $this->defaultNamespace = 'App\\Http\\Controller\\';

        $this->pathParam = [];
        $this->middleware = [];
        $this->parseSuccess = FALSE;
    }

    /**
     * 获取请求路径
     *
     * @return string
     */
    public function getPathInfo()
    {
        return $this->pathInfo;
    }

    /**
     * 获取被调用控制器
     * Get the called controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * 获取被调用控制器方法
     * Get the called controller method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * 获取路由配置
     *
     * @return array
     */
    public function getRouteConfig()
    {
        return $this->routeConfig;
    }

    /**
     * 获取路由索引
     *
     * @return array
     */
    public function getRouteIndex()
    {
        return $this->routeIndex;
    }

    /**
     * 获取中间件
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * 获取路径参数
     *
     * @return array
     */
    public function getPathParam()
    {
        return $this->pathParam;
    }

    /**
     * 执行
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

        // 解析路由 Parse route
        if (config('app.route_by_config')) {
            $this->parseRouteByConfig();
        }
        if (!$this->parseSuccess && config('app.route_by_path')) {
            $this->parseRouteByPath();
        }

        // 检查控制器 Check controller
        if (!$this->parseSuccess) {
            throw new httpException(404, 'Route not found. ' . $this->pathInfo);
        }
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->method)) {
            throw new httpException(404, 'Controller not found: ' . $this->pathInfo . ' ' . $this->controller . '->' . $this->method);
        }

        // 钩子-控制器运行前
        Hook::run('run_controller_before', $this->pathParam);

        // 运行控制器和中间件 Run controller and middleware
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
     * 通过配置解析路由
     * Parse route by config
     */
    protected function parseRouteByConfig()
    {
        // 读取缓存，或者构建 Read from cache, or build
        if ($isCache = config('app.cache')) {
            if (empty($this->routeConfig)) {
                $config = config();
                if ($config->has('__SHY_ROUTE_CONFIG')) {
                    $this->routeConfig = config('__SHY_ROUTE_CONFIG');
                    $this->routeIndex = config('__SHY_ROUTE_INDEX');
                } else {
                    $this->buildRoute();

                    $config->set('__SHY_ROUTE_CONFIG', $this->routeConfig);
                    $config->set('__SHY_ROUTE_INDEX', $this->routeIndex);
                }
            }
        } else {
            $this->buildRoute();
        }

        // 解析限域名路由 Parse route with host
        if (isset($this->routeConfig[$this->host])) {
            $this->parseCurrentPathByConfig($this->routeConfig[$this->host], $this->routeIndex[$this->host]);
        }
        // 解析无域名路由 Parse route without host
        if (!$this->parseSuccess && isset($this->routeConfig[''])) {
            $this->parseCurrentPathByConfig($this->routeConfig[''], $this->routeIndex['']);
        }
    }

    /**
     * 构建路由，根据配置文件
     */
    public function buildRoute()
    {
        $this->routeConfig = $this->routeIndex = [];

        $router = config('router');

        // 简单路径 Simple path
        if (isset($router['path']) && is_array($router['path'])) {
            foreach ($router['path'] as $path => $handle) {
                $this->assembleRoute('', $path, $this->defaultNamespace, $handle);
            }
        }

        // 组路径 Group path
        if (isset($router['group']) && is_array($router['group'])) {
            foreach ($router['group'] as $group) {
                if (!isset($group['path']) || !is_array($group['path'])) {
                    continue;
                }

                $host = '';
                if (isset($group['host'])) {
                    $host = $group['host'];
                }

                $prefix = '';
                if (isset($group['prefix']) && is_string($group['prefix'])) {
                    $prefix = trim($group['prefix'], " \/\t\n\r\0\x0B") . '/';
                }

                $namespace = $this->defaultNamespace;
                if (isset($group['namespace']) && is_string($group['namespace'])) {
                    $namespace = trim($group['namespace'], " \\\/\t\n\r\0\x0B") . '\\';
                }

                $middleware = [];
                if (isset($group['middleware']) && is_array($group['middleware'])) {
                    $middleware = $this->getMiddlewareFullNameByConfig($group['middleware']);
                }

                foreach ($group['path'] as $path => $handle) {
                    $path = $prefix . trim($path, " \/\t\n\r\0\x0B");

                    $this->assembleRoute($host, $path, $namespace, $handle, $middleware);
                }
            }
        }
    }

    /**
     * 组装路由
     * Assemble the build route
     *
     * @param string|array $host
     * @param string $path
     * @param string $namespace
     * @param string $handle
     * @param array $middleware
     */
    protected function assembleRoute($host, string $path, string $namespace, string $handle, array $middleware = [])
    {
        $path = trim($path, " \/\t\n\r\0\x0B");
        $handle = trim($handle, " \\\/\t\n\r\0\x0B");

        $routeConfig = [
            'hl' => $namespace . ucfirst($handle),
        ];

        if (!empty($middleware)) {
            $routeConfig['mw'] = $middleware;
        }

        $this->assembleRouteConfig($host, $path, $routeConfig);
        $this->assembleRouteIndex($host, $path);
    }

    /**
     * 组装路由配置
     *
     * @param $host
     * @param string $path
     * @param array $routeConfig
     */
    protected function assembleRouteConfig($host, string $path, array $routeConfig)
    {
        if (is_string($host)) {
            if ($host && preg_match("/^(http:\/\/|https:\/\/)?([^\/]+)/i", $host, $matches)) {
                $host = $matches[2] ?? '';
            }

            $this->routeConfig[$host][$path] = $routeConfig;
            return;
        }

        if (is_array($host)) {
            foreach ($host as $oneHost) {
                $this->assembleRouteConfig($oneHost, $path, $routeConfig);
            }
        }
    }

    /**
     * 组装路由索引
     *
     * @param $host
     * @param string $path
     */
    protected function assembleRouteIndex($host, string $path)
    {
        if (is_string($host)) {
            if ($host && preg_match("/^(http:\/\/|https:\/\/)?([^\/]+)/i", $host, $matches)) {
                $host = $matches[2] ?? '';
            }

            // 路径分节 Path Segmentation
            $paths = explode('/', $path);
            $count = count($paths);
            if (!isset($this->routeIndex[$host][$count])) {
                $this->routeIndex[$host][$count] = [];
            }

            // 组装层级数组作为索引 Assemble the hierarchical array as index
            $currentIndex = &$this->routeIndex[$host][$count];
            foreach ($paths as $currentPath) {
                if (!isset($currentIndex[$currentPath])) {
                    $currentIndex[$currentPath] = [];
                }

                $currentIndex = &$currentIndex[$currentPath];
            }

            return;
        }

        if (is_array($host)) {
            foreach ($host as $oneHost) {
                $this->assembleRouteIndex($oneHost, $path);
            }
        }
    }

    /**
     * 获取中间件全名
     *
     * @param $shortNames
     * @return array
     */
    protected function getMiddlewareFullNameByConfig(array $shortNames)
    {
        $config = config('middleware');

        $middleware = [];
        foreach ($shortNames as $shortName) {
            list($name, $param) = array_pad(explode(':', $shortName, 2), 2, null);

            $param = isset($param) ? ':' . $param : '';

            if (isset($config[$name])) {
                // 支持中间件组 Support middleware group
                if (is_array($config[$name])) {
                    $group = array_map(function ($name) use ($param) {
                        return $name . $param;
                    }, $config[$name]);

                    $middleware = array_merge($middleware, $group);
                } else {
                    $middleware[] = $config[$name] . $param;
                }
            } else {
                // 不存在时视为全名 Treated as full name if not exist
                $middleware[] = $name . $param;
            }
        }

        return array_filter($middleware);
    }

    /**
     * 执行解析当前路径
     *
     * @param array $routeConfig
     * @param array $routeIndex
     */
    protected function parseCurrentPathByConfig(array $routeConfig, array $routeIndex)
    {
        if (isset($routeConfig[$this->pathInfo])) {
            $this->makeRouteConfigTakeEffect($routeConfig[$this->pathInfo]);
            return;
        }

        $paths = explode('/', $this->pathInfo);
        $count = count($paths);

        if (isset($routeIndex[$count])) {
            $currentIndex = $routeIndex[$count];
            $success = true;
            $matchPath = [];
            $matchParam = [];
            foreach ($paths as $path) {
                if (isset($currentIndex[$path])) {
                    $matchPath[] = $path;
                    $currentIndex = $currentIndex[$path];
                } elseif (isset($currentIndex['?'])) {
                    $matchPath[] = '?';
                    $matchParam[] = $path;
                    $currentIndex = $currentIndex['?'];
                } else {
                    $success = false;
                    break;
                }
            }

            $matchPath = implode('/', $matchPath);
            if ($success && empty($currentIndex) && isset($routeConfig[$matchPath])) {
                $this->makeRouteConfigTakeEffect($routeConfig[$matchPath], $matchParam);
            }
        }
    }

    /**
     * 使配置路由生效
     * Make the config route take effect
     *
     * @param $config
     * @param array $pathParam
     */
    protected function makeRouteConfigTakeEffect($config, array $pathParam = [])
    {
        list($this->controller, $this->method) = array_pad(explode('@', $config['hl']), 2, 'index');

        $this->middleware = $config['mw'] ?? [];
        $this->pathParam = $pathParam;

        $this->parseSuccess = TRUE;
    }

    /**
     * 通过路径解析路由
     * Parse route by path
     */
    protected function parseRouteByPath()
    {
        $this->controller = config('app.default_controller');
        $this->method = 'index';

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

        $this->parseSuccess = TRUE;
    }

    /**
     * 运行控制器
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
