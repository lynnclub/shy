<?php

namespace Shy\Http\Exception;

use Shy\Contract\Config;
use Shy\Contract\ExceptionHandler;
use Shy\Contract\Logger;
use Shy\Exception\Cache\InvalidArgumentException;
use Shy\Http\Contract\Response;
use Shy\Http\Contract\View;
use Throwable;

class Handler implements ExceptionHandler
{
    /**
     * @var Throwable
     */
    protected $throwable;

    /**
     * 设置可抛出错误
     * Set Throwable
     *
     * @param Throwable $throwable
     */
    public function set(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * 处理日志
     * Logging
     *
     * @param Logger $logger
     */
    public function logging(Logger $logger)
    {
        if ($this->throwable instanceof HttpException) {
            $logger->info('Http Code ' . $this->throwable->getStatusCode(), [
                'Message: ' . $this->throwable->getMessage(),
                'File: ' . $this->throwable->getFile() . ' line ' . $this->throwable->getLine(),
            ]);
        } else {
            $logger->error('Exception', get_throwable_array($this->throwable));
        }
    }

    /**
     * 处理报告
     * Report
     */
    public function report()
    {
        if (method_exists($this->throwable, 'report')) {
            $this->throwable->report();
        }
    }

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
    public function response(Config $config = null, Response $response = null, View $view = null)
    {
        if (is_null($response) || is_null($view)) {
            echo implode(PHP_EOL, get_throwable_array($this->throwable));
            return;
        }

        $view->initialize();

        if ($this->throwable instanceof HttpException) {
            $response->withStatus($this->throwable->getStatusCode())
                ->withHeaders($this->throwable->getHeaders());

            if ($this->throwable->getStatusCode() === 404) {
                $response->output($view->view('errors/404'));
            } else {
                $response->output($view->view('errors/common')->with(['e' => $this->throwable]));
            }
        } else {
            $response->withStatus(500)
                ->withoutHeader(null)
                ->output($config->find('app.debug') ? $view->view('errors/exception')->with(['e' => $this->throwable]) : '');
        }
    }
}
