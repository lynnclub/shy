<?php
/**
 * Http functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('lang')) {
    /**
     * lang
     *
     * @param int $code
     * @return string
     */
    function lang(int $code)
    {
        $language = Shy\Http\Facades\Session::get('language');
        if (empty($language)) {
            $language = config('app.default_lang');
        }

        return config('lang/' . $language . '.' . $code);
    }
}

if (!function_exists('set_lang')) {
    /**
     * Set Lang
     *
     * @param string $language
     */
    function set_lang(string $language)
    {
        shy(Shy\Http\Contracts\Session::class)->set('language', $language);
    }
}

if (!function_exists('url')) {
    /**
     * Get url
     *
     * @param string $path
     * @return string
     */
    function url(string $path = '')
    {
        $path = trim($path, ' /');
        $router = config('__ROUTE_INDEX.' . $path);
        if (empty($router)) {
            throw new RuntimeException('Path "' . $path . '" not found in router config.');
        }

        return BASE_URL . $path;
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
     * Generate a CSRF token form field.
     *
     * @return string
     */
    function csrf_field()
    {
        return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
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
        return shy(Shy\Http\Contracts\Session::class)->token();
    }
}

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
        $smarty = shy(Smarty::class);
        if (!is_object($smarty)) {
            throw new RuntimeException('Class Smarty not found.');
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
            $smarty->debugging = true;
        }

        return $smarty->fetch($view, $params);
    }
}
