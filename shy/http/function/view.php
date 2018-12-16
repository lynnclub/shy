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
     * @param $view
     * @param array $params
     * @param string $layout
     * @return mixed
     */
    function view($view, $params = [], $layout = '')
    {
        $view = make_new('view', 'shy\http\view')->view($view);
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
     * @param $filename
     */
    function include_view($filename)
    {
        $filename = APP_PATH . 'http/views/' . $filename . '.php';
        if (file_exists($filename)) {
            $params = shy('view')->getParams();
            if (!empty($params)) {
                extract($params);
            }

            ob_start();
            require "{$filename}";
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
        $subViewContent = shy('view')->getSubView();
        if (empty($subViewContent) || !is_string($subViewContent)) {
            shy('view')->error('[view] Include sub view failed.');
        } else {
            echo $subViewContent;
        }
    }
}

if (!function_exists('param')) {
    /**
     * output param
     *
     * @param $key
     */
    function param($key)
    {
        $params = shy('view')->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            echo $params[$key];
        } elseif (defined($key)) {
            echo constant($key);
        } else {
            shy('view')->error('[view] Param ' . $key . ' is not exist.');
        }
    }
}

if (!function_exists('smarty')) {
    /**
     * New view
     *
     * @param $view
     * @param array $params
     * @return mixed
     */
    function smarty($view, $params = [])
    {
        if (config('smarty')) {
            $params['GLOBALS'] = $GLOBALS;
            return shy('smarty')->fetch($view, $params);
        } else {
            throw new RuntimeException('The Smarty module is closed and can be opened in the configuration file.');
        }
    }
}
