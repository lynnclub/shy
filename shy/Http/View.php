<?php

namespace Shy\Http;

use Shy\Http\Contracts\View as ViewContract;
use Shy\Core\Contracts\Config;
use RuntimeException;

class View implements ViewContract
{
    /**
     * @var string
     */
    protected $viewPath;

    /**
     * View file
     *
     * @var string
     */
    protected $view;

    /**
     * Subview parse content
     *
     * @var string
     */
    protected $subViewContent;

    /**
     * Layout file
     *
     * @var string
     */
    protected $layout;

    /**
     * Params pass by controller
     *
     * @var array
     */
    protected $params = [];

    /**
     * Error message
     *
     * @var string
     */
    protected $errorMsg = '';

    /**
     * View constructor.
     *
     * @param Config $config
     *
     * @throws \Shy\Core\Exceptions\Cache\InvalidArgumentException
     */
    public function __construct(Config $config)
    {
        $this->viewPath = $config->find('path.view');
    }

    /**
     * Initialize in cycle
     */
    public function initialize()
    {
        $this->view = null;
        $this->subViewContent = null;
        $this->layout = null;
        $this->params = [];
        $this->errorMsg = '';
    }

    /**
     * Set view file
     *
     * @param string $view
     * @return $this
     */
    public function view(string $view)
    {
        if (empty($view)) {
            throw new RuntimeException('Empty view.');
        }

        $view = $this->viewPath . $view . '.php';
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
     * @param string $layout
     * @return $this
     */
    public function layout(string $layout)
    {
        if (!empty($layout)) {
            $layout = $this->viewPath . 'layout/' . $layout . '.php';
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
     */
    public function render()
    {
        if (empty($this->view)) {
            throw new RuntimeException('View empty.');
        }

        ob_start();
        extract($this->params);

        /**
         * View
         */
        include "{$this->view}";

        /**
         * Layout
         */
        if (isset($this->layout)) {
            $this->subViewContent = ob_get_clean();
            $this->params = array_merge($this->params, get_defined_vars());
            require "{$this->layout}";
        }
        $viewContent = ob_get_contents();

        /**
         * Can not ob_get_clean() or ob_end_clean(), WorkerMan will no output
         */
        ob_clean();

        if (empty($this->errorMsg)) {
            $this->initialize();

            return $viewContent;
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
