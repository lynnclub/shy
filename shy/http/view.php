<?php

/**
 * Shy Framework View
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http;

use RuntimeException;

class view
{

    /**
     * View file
     *
     * @var string
     */
    private $view;

    /**
     * View parse content
     *
     * @var string
     */
    private $viewContent;

    /**
     * Subview parse content
     *
     * @var string
     */
    private $subViewContent;

    /**
     * Layout file
     *
     * @var string
     */
    private $layout;

    /**
     * Params pass by controller
     *
     * @var array
     */
    private $params = [];

    /**
     * Error message
     *
     * @var string
     */
    private $errorMsg = '';

    /**
     * Set view file
     *
     * @param $view
     * @return $this
     */
    public function view($view)
    {
        if (empty($view) || !is_string($view)) {
            throw new RuntimeException('Invalid view.');
        }

        $view = config('app', 'path') . 'http/views/' . $view;
        if (file_exists($view . '.php')) {
            $this->view = $view;
        } else {
            throw new RuntimeException('View file ' . $view . '.php not exist.');
        }

        return $this;
    }

    /**
     * Set layout file
     *
     * @param $layout
     * @return $this
     */
    public function layout($layout)
    {
        if (!empty($layout)) {
            if (!is_string($layout)) {
                throw new RuntimeException('Invalid layout.');
            }
            $layout = config('app', 'path') . 'http/views/layout/' . $layout;
            if (file_exists($layout . '.php')) {
                $this->layout = $layout;
            } else {
                throw new RuntimeException('Layout file ' . $layout . '.php not exist.');
            }
        }

        return $this;
    }

    /**
     * Params pass by controller
     *
     * @param array $params
     * @return $this
     */
    public function with(array $params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    /**
     * Render view
     *
     * @return string
     */
    public function render()
    {
        ob_start();
        extract($this->params);
        if (isset($this->layout, $this->view)) {
            include $this->view . '.php';
            $this->subViewContent = ob_get_contents();
            ob_clean();
            include $this->layout . '.php';
            $this->viewContent = ob_get_contents();
        } elseif (isset($this->view)) {
            include $this->view . '.php';
            $this->viewContent = ob_get_contents();
        }
        ob_end_clean();

        if (empty($this->errorMsg)) {
            return $this->viewContent;
        } else {
            throw new RuntimeException($this->errorMsg);
        }
    }

    /**
     * Get subview content
     *
     * @return string
     */
    public function getSubView()
    {
        return $this->subViewContent;
    }

    /**
     * Get params pass by controller
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Error handle
     *
     * @param $msg
     */
    public function error($msg)
    {
        $this->errorMsg = $msg;
    }

}