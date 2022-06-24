<?php

namespace Shy\Http\Contract;

interface View
{
    /**
     * 设置视图文件
     * Set view file
     *
     * @param string $view
     * @return $this
     */
    public function view(string $view);

    /**
     * 设置布局文件
     * Set layout file
     *
     * @param string $layout
     * @return $this
     */
    public function layout(string $layout);

    /**
     * 设置控制器传递的参数
     * Set params pass by controller
     *
     * @param array $params
     * @return $this
     */
    public function with(array $params);

    /**
     * 渲染视图
     * Render view
     *
     * @return string
     */
    public function render();

    /**
     * 循环初始化
     * Loop initialize
     */
    public function initialize();
}
