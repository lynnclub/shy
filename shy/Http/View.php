<?php

namespace Shy\Http;

use RuntimeException;
use Shy\Http\Contract\View as ViewContract;

class View implements ViewContract
{
    /**
     * 视图路径
     * View path
     *
     * @var string
     */
    protected $viewPath;

    /**
     * 视图文件名
     * View filename
     *
     * @var string
     */
    protected $view;

    /**
     * 子视图文件名
     * Subview filename
     *
     * @var string
     */
    protected $subView;

    /**
     * 布局文件名
     * Layout filename
     *
     * @var string
     */
    protected $layout;

    /**
     * 控制器传递的参数
     * Params pass by controller
     *
     * @var array
     */
    protected $params = [];

    /**
     * 错误消息
     * Error message
     *
     * @var string
     */
    protected $errorMsg = '';

    /**
     * View constructor.
     *
     * @param string|null $viewPath
     */
    public function __construct(string $viewPath = null)
    {
        if (empty($viewPath)) {
            $this->viewPath = VIEW_PATH;
        } else {
            $this->viewPath = $viewPath;
        }
    }

    /**
     * 设置视图文件
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
     * 设置布局文件
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
     * 设置控制器传递的参数
     * Set params pass by controller
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
     * 渲染视图
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
         * 带布局或单视图
         * With layout or single view
         */
        if (isset($this->layout)) {
            $this->subView = $this->view;

            include "{$this->layout}";
        } else {
            include "{$this->view}";
        }
        $viewContent = ob_get_contents();

        /**
         * Can not use ob_get_clean() or ob_end_clean(), WorkerMan will no output
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
     * 获取子视图内容
     * Get subview content
     *
     * @return string
     */
    public function getSubView()
    {
        return $this->subView;
    }

    /**
     * 获取布局文件名
     * Get layout filename
     *
     * @return string
     */
    public function getLayout()
    {
        return $this->layout;
    }

    /**
     * 获取视图文件名
     * Get view filename
     *
     * @return string
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * 获取控制器传递的参数
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

    /**
     * 循环初始化
     * Loop initialize
     */
    public function initialize()
    {
        $this->view = null;
        $this->subView = null;
        $this->layout = null;
        $this->params = [];
        $this->errorMsg = '';
    }
}
