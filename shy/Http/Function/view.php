<?php
/**
 * View functions
 */

use Shy\Contract\Config;
use Shy\Http\Contract\View;

if (!function_exists('view')) {
    /**
     * New view
     *
     * @param string $view
     * @param array $params
     * @param string $layout
     *
     * @return View
     */
    function view(string $view, array $params = [], string $layout = '')
    {
        $engine = shy(View::class);

        if ($engine instanceof View) {
            $engine->initialize();
        } else {
            throw new \RuntimeException('Class View is not an instance.');
        }

        $engine->view($view);

        if ($params) {
            $engine->with($params);
        }
        if ($layout) {
            $engine->layout($layout);
        }

        return $engine;
    }
}

if (!function_exists('include_view')) {
    /**
     * Include view
     *
     * @param string $filename
     */
    function include_view(string $filename = '')
    {
        $engine = shy(View::class);

        if ($filename) {
            $filename = VIEW_PATH . $filename . '.php';
        } else {
            $filename = $engine->getSubView();
        }

        if (file_exists($filename)) {
            extract($engine->getParams());

            include "{$filename}";
            unset($filename);

            $engine->with(get_defined_vars());
        } else {
            $engine->error($filename . ' is not exist.');
        }
    }
}

if (!function_exists('get_param')) {
    /**
     * get param
     *
     * @param string $key
     * @param bool $allow_not_exist
     *
     * @return mixed
     */
    function get_param(string $key, bool $allow_not_exist = TRUE)
    {
        $engine = shy(View::class);

        $keyLevels = explode('.', $key);
        $firstKey = array_shift($keyLevels);

        $params = $engine->getParams();
        if (isset($params[$firstKey])) {
            return get_array_key($keyLevels, $params[$firstKey]);
        } elseif (isset($GLOBALS[$firstKey])) {
            return get_array_key($keyLevels, $GLOBALS[$firstKey]);
        } elseif (defined($firstKey)) {
            return constant($firstKey);
        } elseif (!$allow_not_exist) {
            $engine->error($key . ' is not exist.');
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

            $config = shy(Config::class);

            $old = $config->get('__PUSH__RESOURCE_' . $id);
            if (empty($old)) {
                $old = [];
            }

            if (!is_array($old)) {
                throw new \InvalidArgumentException($id . ' value type error.');
            }
            array_push($old, $resource);
            $old = array_unique($old);

            $config->set('__PUSH__RESOURCE_' . $id, $old);

            if (empty($resource) && is_array($old) && !empty($old)) {
                $config->delete('__PUSH__RESOURCE_' . $id);

                echo implode('', $old);
            }
        }
    }
}
