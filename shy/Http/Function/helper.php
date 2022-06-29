<?php

use Shy\Http\Contract\Request;
use Shy\Http\Contract\Response;
use Shy\Http\Contract\Router;
use Shy\Http\Contract\Session;

if (!function_exists('smarty')) {
    /**
     * 创建smarty视图
     * Smarty new view
     *
     * @param string $view
     * @param mixed $params
     * @return mixed
     */
    function smarty(string $view, $params = [])
    {
        $smarty = shy(\Smarty::class);
        if (!is_object($smarty)) {
            throw new \RuntimeException('Class Smarty not found.');
        }

        $config = config('smarty');
        $smarty->template_dir = VIEW_PATH;
        $smarty->compile_dir = CACHE_PATH . 'app/smarty' . DIRECTORY_SEPARATOR;
        $smarty->left_delimiter = $config['left_delimiter'];
        $smarty->right_delimiter = $config['right_delimiter'];
        $smarty->caching = $config['caching'];
        $smarty->cache_lifetime = $config['cache_lifetime'];
        $params['GLOBALS'] = $GLOBALS;

        if (config('app.debug')) {
            $smarty->debugging = TRUE;
        }

        return $smarty->fetch($view, $params);
    }
}

if (!function_exists('url')) {
    /**
     * 获取url
     * Get url
     *
     * @param string $path
     * @param string $base_url
     * @return string
     */
    function url(string $path = '', string $base_url = '')
    {
        $path = trim($path, " \/\t\n\r\0\x0B");

        if (empty($base_url)) {
            if (empty($base_url = config('app.base_url'))) {
                $request = shy(Request::class);
                if (is_object($request)) {
                    $base_url = $request->getUriForPath('/');
                } else {
                    $base_url = '';
                }
            }
        }
        $base_url = trim($base_url, " \/\t\n\r\0\x0B");

        if (preg_match("/^(\/\/|http:\/\/|https:\/\/){1}([^\/]+)/i", $base_url, $matches) && !empty($matches[2])) {
            $host = $matches[2];
        } else {
            throw new \RuntimeException('Can not handle scheme and host.');
        }

        $pathArray = explode('/', $path);
        $sectionNum = count($pathArray);

        $router = shy(Router::class);
        if (!is_object($router)) {
            throw new \RuntimeException('Router contract is not bound.');
        }

        $routeIndex = $router->getRouteIndex();
        if (empty($routeIndex)) {
            $router->buildRoute();
            $routeIndex = $router->getRouteIndex();
        }

        if (isset($routeIndex[$host][$sectionNum])) {
            $sectionRouteIndex = $routeIndex[$host][$sectionNum];

            $matchPath = [];
            foreach ($pathArray as $path) {
                if (isset($sectionRouteIndex[$path])) {
                    $matchPath[] = $path;
                    $sectionRouteIndex = $sectionRouteIndex[$path];
                } elseif (isset($sectionRouteIndex['?'])) {
                    $matchPath[] = $path;
                    $sectionRouteIndex = $sectionRouteIndex['?'];
                } else {
                    break;
                }
            }

            if (count($matchPath) === $sectionNum) {
                $validPath = implode('/', $matchPath);
            }
        }

        if (empty($validPath) && isset($routeIndex[''][$sectionNum])) {
            $sectionRouteIndex = $routeIndex[''][$sectionNum];

            $matchPath = [];
            foreach ($pathArray as $path) {
                if (isset($sectionRouteIndex[$path])) {
                    $matchPath[] = $path;
                    $sectionRouteIndex = $sectionRouteIndex[$path];
                } elseif (isset($sectionRouteIndex['?'])) {
                    $matchPath[] = $path;
                    $sectionRouteIndex = $sectionRouteIndex['?'];
                } else {
                    break;
                }
            }

            if (count($matchPath) === $sectionNum) {
                $validPath = implode('/', $matchPath);
            }
        }

        if (isset($validPath)) {
            return $base_url . '/' . $validPath;
        } else {
            throw new \RuntimeException('Path "' . $path . '" not found in router index.');
        }
    }
}

if (!function_exists('redirect')) {
    /**
     * 跳转
     * Redirect
     *
     * @param string $url
     * @return Response
     */
    function redirect(string $url = '')
    {
        if (empty($url)) {
            $url = url();
        }

        return shy(Response::class)->withStatus(302)->withHeader('Location', $url);
    }
}

if (!function_exists('xss_clean')) {
    /**
     * 清除XSS
     * XSS Clean
     *
     * @param string|array $data
     * @return string|array
     */
    function xss_clean($data)
    {
        if (is_array($data)) {
            $clean = [];
            foreach ($data as $key => $value) {
                $clean[xss_clean($key)] = xss_clean($value);
            }

            return $clean;
        }

        return htmlspecialchars(strip_tags($data));
    }
}

if (!function_exists('csrf_field')) {
    /**
     * 生成CSRF口令表单域
     * Generate a CSRF token form field.
     *
     * @param string $name
     * @return string
     */
    function csrf_field(string $name = '')
    {
        return '<input type="text" hidden="hidden" name="csrf-token" value="' . csrf_token($name) . '">';
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * 生成CSRF口令meta标签
     * Generate a CSRF token meta tag.
     *
     * @param string $name
     * @return string
     */
    function csrf_meta(string $name = '')
    {
        return '<meta name="csrf-token" content="' . csrf_token($name) . '">';
    }
}

if (!function_exists('csrf_token')) {
    /**
     * 生成CSRF口令
     * Get the CSRF token value.
     *
     * @param string $name
     * @return string
     */
    function csrf_token(string $name = '')
    {
        return shy(Session::class)->token('__csrf_token' . empty_or_splice($name, ':'));
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * 验证CSRF口令
     * Verify CSRF token.
     *
     * @param string $token
     * @param string $name
     * @return bool
     */
    function csrf_verify(string $token, string $name = '')
    {
        if (empty($token)) {
            return FALSE;
        }

        $sessionToken = shy(Session::class)->get('__csrf_token' . empty_or_splice($name, ':'));
        if ($token === $sessionToken) {
            return TRUE;
        }

        return FALSE;
    }
}

if (!function_exists('is_valid_ip')) {
    /**
     * 验证IP
     * Is valid IP
     *
     * @param string $ip
     * @return bool
     */
    function is_valid_ip(string $ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return TRUE;
        }

        return FALSE;
    }
}

if (!function_exists('get_valid_ips')) {
    /**
     * 过滤非法IP
     * Get valid ips
     *
     * @param array $ips
     * @return array
     */
    function get_valid_ips(array $ips)
    {
        $validIps = [];

        foreach ($ips as $ip) {
            if (is_array($ip)) {
                $validIps = array_merge($validIps, get_valid_ips($ip));
            } elseif (is_string($ip)) {
                if (stripos($ip, ',') !== FALSE) {
                    $validIps = array_merge($validIps, get_valid_ips(explode(',', $ip)));
                } else if (is_valid_ip($ip)) {
                    $validIps[] = trim($ip);
                }
            }
        }

        return array_filter(array_unique($validIps));
    }
}

if (!function_exists('get_response_json')) {
    /**
     * 获取响应json
     * Get response json
     *
     * @param int $code
     * @param string|array $msg
     * @param array $data
     * @return string
     */
    function get_response_json(int $code, $msg = null, array $data = array())
    {
        if ($msg === null) {
            $msg = lang($code);
        }

        return json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data), JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('url_chinese_encode')) {
    /**
     * 中文url编码
     * Url chinese encode
     *
     * @param string $url
     * @return string
     */
    function url_chinese_encode(string $url)
    {
        $uri = '';
        $cs = unpack('C*', $url);
        $len = count($cs);

        for ($i = 1; $i <= $len; $i++) {
            $uri .= $cs[$i] > 127 ? '%' . strtoupper(dechex($cs[$i])) : $url[$i - 1];
        }

        return $uri;
    }
}

if (!function_exists('is_mobile')) {
    /**
     * 是否移动设备
     * Is mobile
     *
     * @return bool
     */
    function is_mobile()
    {
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
            $client_keywords = [
                //common
                'mobile',
                'wap',
                //os
                'android',
                'blackberry',
                'ios',
                'openwave',
                'palm',
                'symbian',
                'windows ce',
                //browser
                'mqqbrowser',
                'operamobi',
                'operamini',
                'ucweb',
                'fennec',
                'netfront',
                //device
                'ipad',
                'iphone',
                'ipod',
                'acer',
                'asus',
                'coolpad',
                'etouch',
                'nokia',
                'sony',
                'ericsson',
                'huawei',
                'mi',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'meizu',
                'nexus',
            ];

            if (preg_match("/(" . implode('|', $client_keywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return TRUE;
            }
        }

        return FALSE;
    }
}
