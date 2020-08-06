<?php
/**
 * View functions
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
        if ($object instanceof \Shy\Http\Contracts\View) {
            $object->initialize();
        } else {
            throw new \RuntimeException('view() Class View is not an instance.');
        }

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
    function include_view(string $filename = '')
    {
        if (empty($filename)) {
            $filename = shy('view')->getSubView();
        } else {
            $filename = VIEW_PATH . $filename . '.php';
        }

        if (file_exists($filename)) {
            extract(shy('view')->getParams());

            include "{$filename}";
            unset($filename);

            shy('view')->with(get_defined_vars());
        } else {
            shy('view')->error('include_view() file ' . $filename . ' is not exist.');
        }
    }
}

if (!function_exists('get_param')) {
    /**
     * get param
     *
     * @param string $key
     * @param bool $allow_not_exist
     * @return mixed
     */
    function get_param(string $key, bool $allow_not_exist = TRUE)
    {
        $params = shy('view')->getParams();
        if (isset($params[$key]) && (is_string($params[$key]) || is_numeric($params[$key]))) {
            return $params[$key];
        } elseif (isset($GLOBALS[$key])) {
            return $GLOBALS[$key];
        } elseif (defined($key)) {
            return constant($key);
        } elseif (!$allow_not_exist) {
            shy('view')->error('get_param() Param ' . $key . ' is not exist.');
        }
    }
}

if (!function_exists('param')) {
    /**
     * Echo param
     *
     * @param string $key
     * @param bool $allow_not_exist
     */
    function param(string $key, bool $allow_not_exist = TRUE)
    {
        echo get_param($key, $allow_not_exist);
    }
}

if (!function_exists('param_array')) {
    /**
     * Echo param in array
     *
     * @param string
     * @param string
     * @param bool $allow_not_exist
     */
    function param_array(string $arrayKey, string $key, bool $allow_not_exist = TRUE)
    {
        $params = shy('view')->getParams();
        if (isset($params[$arrayKey][$key]) && (is_string($params[$arrayKey][$key]) || is_numeric($params[$arrayKey][$key]))) {
            echo $params[$arrayKey][$key];
        } elseif (isset($GLOBALS[$arrayKey][$key])) {
            echo $GLOBALS[$arrayKey][$key];
        } elseif (!$allow_not_exist) {
            shy('view')->error('param_array() Param array ' . $arrayKey . ' key ' . $key . ' is not exist.');
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

            $old = shy('config')->get('__PUSH__RESOURCE_' . $id);
            if (empty($old)) {
                $old = [];
            }
            if (!is_array($old)) {
                throw new \InvalidArgumentException('push_resource() Resource value of id ' . $id . ' type error.');
            }
            array_push($old, $resource);
            $old = array_unique($old);
            shy('config')->set('__PUSH__RESOURCE_' . $id, $old);

            if (empty($resource) && is_array($old) && !empty($old)) {
                shy('config')->delete('__PUSH__RESOURCE_' . $id);
                echo implode('', $old);
            }
        }
    }
}
