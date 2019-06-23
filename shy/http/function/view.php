<?php
/**
 * Http view functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('view')) {
    /**
     * New view
     *
     * @param string $view
     * @param array $params
     * @param string $layout
     * @return \shy\http\view
     */
    function view(string $view, array $params = [], string $layout = '')
    {
        $view = make_new(shy\http\view::class)->view($view);
        if (isset($params)) {
            $view->with($params);
        }
        if (isset($layout)) {
            $view->layout($layout);
        }
        return $view;
    }
}

if (!function_exists('include_view')) {
    /**
     * Include View
     *
     * @param string $filename
     */
    function include_view(string $filename)
    {
        $filename = APP_PATH . 'http/views/' . $filename . '.php';
        if (file_exists($filename)) {
            $params = shy(\shy\http\view::class)->getParams();
            if (!empty($params)) {
                extract($params);
            }

            ob_start();
            include "{$filename}";
            ob_end_flush();
        } else {
            shy(\shy\http\view::class)->error('[view] Include view ' . $filename . ' is not exist.');
        }
    }
}

if (!function_exists('include_sub_view')) {
    /**
     * Include sub view
     */
    function include_sub_view()
    {
        $view = shy(\shy\http\view::class);
        $subViewContent = $view->getSubView();
        if (empty($subViewContent) || !is_string($subViewContent)) {
            $view->error('[view] Layout ' . $view->getLayout() . ' include sub view ' . $view->getView() . ' failed.');
        } else {
            echo $subViewContent;
        }
    }
}

if (!function_exists('param')) {
    /**
     * output param
     *
     * @param string $key
     * @param bool $allowNotExist
     */
    function param(string $key, bool $allowNotExist = false)
    {
        $params = shy(\shy\http\view::class)->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            echo $params[$key];
        } elseif (isset($GLOBALS[$key])) {
            echo $GLOBALS[$key];
        } elseif (defined($key)) {
            echo constant($key);
        } elseif (!$allowNotExist) {
            shy(\shy\http\view::class)->error('[view] Param ' . $key . ' is not exist.');
        }
    }
}

if (!function_exists('get_param')) {
    /**
     * get param
     *
     * @param string $key
     * @param bool $allowNotExist
     * @return mixed
     */
    function get_param(string $key, bool $allowNotExist = false)
    {
        $params = shy(\shy\http\view::class)->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            return $params[$key];
        } elseif (isset($GLOBALS[$key])) {
            return $GLOBALS[$key];
        } elseif (defined($key)) {
            return constant($key);
        } elseif (!$allowNotExist) {
            shy(\shy\http\view::class)->error('[view] Param ' . $key . ' is not exist.');
        }
    }
}

if (!function_exists('push_resource')) {
    /**
     * Push resource
     *
     * type support js, css
     *
     * @param string $type
     * @param string $resource
     */
    function push_resource(string $type, $resource = '')
    {
        if (is_array($resource)) {
            foreach ($resource as $item) {
                push_resource($type, $item);
            }
        } else {
            if (!empty($resource)) {
                switch ($type) {
                    case 'js':
                        $resource = '<script type="application/javascript" src="' . $resource . '"></script>';
                        break;
                    case 'css':
                        $resource = '<link type="text/css" rel="stylesheet" href="' . $resource . '">';
                        break;
                    default:
                        throw new RuntimeException('push_resource() undefined type ' . $type);
                }
            }

            $array = config_array_push('push_' . $type, $resource);

            if (empty($resource) && is_array($array) && !empty($array)) {
                echo implode('', $array);
            }
        }
    }
}

if (!function_exists('lang')) {
    /**
     * lang
     *
     * @param int $code
     * @return string
     */
    function lang(int $code)
    {
        $language = shy\http\facade\session::get('language');
        if (empty($language)) {
            $language = config_key('default_lang');
        }

        return config_key($code, 'lang/' . $language);
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
        shy\http\facade\session::set('language', $language);
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
        $config = config('smarty');
        $smarty->template_dir = VIEW_PATH;
        $smarty->compile_dir = CACHE_PATH . 'smarty' . DIRECTORY_SEPARATOR;
        $smarty->left_delimiter = $config['left_delimiter'];
        $smarty->right_delimiter = $config['right_delimiter'];
        $smarty->caching = $config['caching'];
        $smarty->cache_lifetime = $config['cache_lifetime'];
        $params['GLOBALS'] = $GLOBALS;

        if (config_key('env') === 'development') {
            $smarty->debugging = true;
        }

        return $smarty->fetch($view, $params);
    }
}
