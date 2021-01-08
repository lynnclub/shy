<?php

if (!function_exists('smarty')) {
    /**
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
     * Get url
     *
     * @param string $path
     * @param string $base_url
     * @return string
     */
    function url(string $path = '', string $base_url = '')
    {
        $path = trim($path, " \/\t\n\r\0\x0B");
        $base_url = trim($base_url, " \/\t\n\r\0\x0B");

        if (empty($base_url)) {
            $request = shy(\Shy\Http\Contracts\Request::class);

            $base_url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
            $host = $request->getHttpHost();
        } elseif (preg_match("/^(\/\/|http:\/\/|https:\/\/){1}([^\/]+)/i", $base_url, $matches) && !empty($matches[2])) {
            $host = $matches[2];
        } else {
            throw new \RuntimeException('Can not handle scheme and host.');
        }

        $routeIndex = shy(\Shy\Http\Contracts\Router::class)->getRouteIndex();
        if (isset($routeIndex[$host][$path])) {
            $validPath = $path;
        } elseif (isset($routeIndex[''][$path])) {
            $validPath = $path;
        } else {
            // Path with param
            $pathWithoutParam = '';
            $pathParamEnd = strrpos($path, '/');
            if ($pathParamEnd > 0) {
                $pathWithoutParam = substr($path, 0, $pathParamEnd);
            }

            if (isset($routeIndex[$host][$pathWithoutParam], $routeIndex[$host][$pathWithoutParam]['with_param'])) {
                $validPath = $path;
            } elseif (isset($routeIndex[''][$pathWithoutParam], $routeIndex[''][$pathWithoutParam]['with_param'])) {
                $validPath = $path;
            }
        }

        if (isset($validPath)) {
            return $base_url . '/' . $validPath;
        } else {
            throw new \RuntimeException('Path "' . $path . '" not found in router config.');
        }
    }
}

if (!function_exists('xss_clean')) {
    /**
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
     * Generate a CSRF token form field.（（Echo here will cause workerman error.）
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="text" hidden="hidden" name="csrf-token" value="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_meta')) {
    /**
     * Generate a CSRF token meta.（Echo here will cause workerman error.）
     *
     * @return string
     */
    function csrf_meta()
    {
        return '<meta name="csrf-token" content="' . csrf_token() . '">';
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     */
    function csrf_token()
    {
        return shy(\Shy\Http\Contracts\Session::class)->token('csrf-token');
    }
}

if (!function_exists('csrf_verify')) {
    /**
     * Verify CSRF token.
     *
     * @param string $token
     * @return bool
     */
    function csrf_verify(string $token)
    {
        $sessionToken = shy(\Shy\Http\Contracts\Session::class)->get('csrf-token');
        if (!empty($token) && $token === $sessionToken) {
            return TRUE;
        }

        return FALSE;
    }
}

if (!function_exists('is_valid_ip')) {
    /**
     * Is valid ip
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
     * Get response json
     *
     * @param int $code
     * @param string|array $msg
     * @param array $data
     * @return string
     */
    function get_response_json(int $code, $msg = null, $data = array())
    {
        if ($msg === null) {
            $msg = lang($code);
        }

        return json_encode(array('code' => $code, 'msg' => $msg, 'data' => $data), JSON_UNESCAPED_SLASHES);
    }
}

if (!function_exists('url_chinese_encode')) {
    /**
     * Url chinese encode
     *
     * @param $url
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
