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

    private $view;

    private $viewContent;

    private $subViewContent;

    private $layout;

    private $params = [];

    public function view($view)
    {
        if (empty($view) || !is_string($view)) {
            throw new RuntimeException('Invalid view.');
        }

        $view = config('app', 'path') . 'views/' . $view;
        if (file_exists($view . '.php')) {
            $this->view = $view;
        } else {
            throw new RuntimeException('View file ' . $view . '.php not exist.');
        }

        return $this;
    }

    public function layout($layout)
    {
        if (!empty($layout)) {
            if (!is_string($layout)) {
                throw new RuntimeException('Invalid layout.');
            }
            $layout = config('app', 'path') . 'views/layout/' . $layout;
            if (file_exists($layout . '.php')) {
                $this->layout = $layout;
            } else {
                throw new RuntimeException('Layout file ' . $layout . '.php not exist.');
            }
        }

        return $this;
    }

    public function with(array $params)
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function render()
    {
        extract($this->params);
        if (isset($this->layout, $this->view)) {
            ob_start();
            include_once $this->view . '.php';
            $this->subViewContent = ob_get_contents();
            ob_end_clean();
            ob_start();
            include_once $this->layout . '.php';
            $this->viewContent = ob_get_contents();
            ob_end_clean();
            return $this->viewContent;
        } elseif (isset($this->view)) {
            ob_start();
            include_once $this->view . '.php';
            $this->viewContent = ob_get_contents();
            ob_end_clean();
            return $this->viewContent;
        }
    }

    public function getSubView()
    {
        return $this->subViewContent;
    }

}