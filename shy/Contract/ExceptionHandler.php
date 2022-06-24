<?php

namespace Shy\Contract;

use Shy\Exception\Cache\InvalidArgumentException;
use Shy\Http\Contract\Response;
use Shy\Http\Contract\View;
use Throwable;

interface ExceptionHandler
{
    /**
     * 设置可抛出错误
     * Set Throwable
     *
     * @param Throwable $throwable
     */
    public function set(Throwable $throwable);

    /**
     * 处理日志
     * Logging
     *
     * @param Logger $logger
     */
    public function logging(Logger $logger);

    /**
     * 处理报告
     * Report
     */
    public function report();

    /**
     * 处理响应
     * Response
     *
     * @param Config|null $config
     * @param Response|null $response
     * @param View|null $view
     *
     * @throws InvalidArgumentException
     */
    public function response(Config $config = null, Response $response = null, View $view = null);
}
