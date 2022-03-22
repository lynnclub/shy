<?php

namespace Shy\Http\Contract;

use Shy\Http\Contract\Request as RequestContract;
use Shy\Http\View;

interface Router
{
    /**
     * 获取请求路径
     *
     * @return string
     */
    public function getPathInfo();

    /**
     * 获取被调用控制器
     * Get the called controller
     *
     * @return string
     */
    public function getController();

    /**
     * 获取被调用控制器方法
     * Get the called controller method
     *
     * @return string
     */
    public function getMethod();

    /**
     * 获取中间件
     *
     * @return array
     */
    public function getMiddleware();

    /**
     * 获取路径参数
     *
     * @return array
     */
    public function getPathParam();

    /**
     * 执行
     *
     * @param RequestContract $request
     * @return mixed|View|string
     * @throws \Exception
     */
    public function run(RequestContract $request);
}
