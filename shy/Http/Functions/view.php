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
     *
     * @return object
     */
    function view(string $view, array $params = [], string $layout = '')
    {
        $object = shy('view');
        if (!is_object($object)) {
            throw new RuntimeException('View class is not an instance.');
        }

        $object->initialize();

        $object->view($view);

        if (!empty($params)) {
            $object->with($params);
        }
        if (!empty($layout)) {
            $object->layout($layout);
        }

        return $object;
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
        $filename = VIEW_PATH . $filename . '.php';
        if (file_exists($filename)) {
            $params = shy('view')->getParams();
            if (!empty($params)) {
                extract($params);
            }

            ob_start();
            include "{$filename}";
            ob_end_flush();
        } else {
            shy('view')->error('[view] Include view ' . $filename . ' is not exist.');
        }
    }
}

if (!function_exists('include_sub_view')) {
    /**
     * Include sub view
     */
    function include_sub_view()
    {
        $view = shy('view');
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
        $params = shy('view')->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            echo $params[$key];
        } elseif (isset($GLOBALS[$key])) {
            echo $GLOBALS[$key];
        } elseif (defined($key)) {
            echo constant($key);
        } elseif (!$allowNotExist) {
            shy('view')->error('[view] Param ' . $key . ' is not exist.');
        }
    }
}

if (!function_exists('param_array')) {
    /**
     * output param in array
     *
     * @param string
     * @param string
     * @param bool $allowNotExist
     */
    function param_array(string $arrayKey, string $key, bool $allowNotExist = false)
    {
        $params = shy('view')->getParams();
        if (isset($params[$arrayKey][$key]) && (is_string($params[$arrayKey][$key]) || is_numeric($params[$arrayKey][$key]))) {
            echo $params[$arrayKey][$key];
        } elseif (isset($GLOBALS[$arrayKey][$key])) {
            echo $GLOBALS[$arrayKey][$key];
        } elseif (!$allowNotExist) {
            shy('view')->error('[view] Param array ' . $arrayKey . ' key ' . $key . ' is not exist.');
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
        $params = shy('view')->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            return $params[$key];
        } elseif (isset($GLOBALS[$key])) {
            return $GLOBALS[$key];
        } elseif (defined($key)) {
            return constant($key);
        } elseif (!$allowNotExist) {
            shy('view')->error('[view] Param ' . $key . ' is not exist.');
        }
    }
}

if (!function_exists('push_resource')) {
    /**
     * Push resource
     *
     * type support js, css
     *
     * @param string $id
     * @param string|array $resource
     * @param string $type
     */
    function push_resource(string $id, $resource = '', string $type = '')
    {
        if (is_array($resource)) {
            foreach ($resource as $item) {
                if (!empty($item)) {
                    push_resource($id, $item, $type);
                }
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
                }
            }

            $old = shy('config')->get('push_' . $id);
            if (empty($old)) {
                $old = [];
            }
            if (!is_array($old)) {
                throw new InvalidArgumentException('Resource value of id ' . $id . ' type error.');
            }
            array_push($old, $resource);
            $old = array_unique($old);
            shy('config')->set('push_' . $id, $old);

            if (empty($resource) && is_array($old) && !empty($old)) {
                echo implode('', $old);
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
        $language = Shy\Http\Facades\Session::get('language');
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
        shy(Shy\Http\Contracts\Session::class)->set('language', $language);
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

        if (config_key('debug')) {
            $smarty->debugging = true;
        }

        return $smarty->fetch($view, $params);
    }
}