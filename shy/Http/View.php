<?php

namespace Shy\Http;

use Shy\Http\Contracts\View as ViewContract;
use RuntimeException;

class View implements ViewContract
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
     * @param string $view
     * @return view
     */
    public function view(string $view)
    {
        if (empty($view)) {
            throw new RuntimeException('Invalid view.');
        }

        $view = config_key('app', 'path') . 'Http/Views/' . $view . '.php';
        if (file_exists($view)) {
            $this->view = $view;
        } else {
            throw new RuntimeException('View file ' . $view . ' not exist.');
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
            $layout = config_key('app', 'path') . 'http/views/layout/' . $layout . '.php';
            if (file_exists($layout)) {
                $this->layout = $layout;
            } else {
                throw new RuntimeException('Layout file ' . $layout . ' not exist.');
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
     * @throws \Exception
     */
    public function render()
    {
        ob_start();
        extract($this->params);

        /**
         * View
         */
        if (isset($this->view)) {
            include "{$this->view}";
        }
        /**
         * Layout
         */
        if (isset($this->layout)) {
            $this->subViewContent = ob_get_clean();
            $this->params = array_merge($this->params, get_defined_vars());
            require "{$this->layout}";
        }
        $this->viewContent = ob_get_contents();
        /**
         * Can not ob_get_clean() or ob_end_clean(), WorkerMan will no output
         */
        ob_clean();

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
     * Get layout filename
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * Get view filename
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
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
