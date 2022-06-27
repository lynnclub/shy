<?php

namespace Shy\Exception;

use ErrorException;
use Exception;
use ReflectionException;
use Shy\Contract\Config;
use Shy\Contract\ExceptionHandler;
use Shy\Contract\Logger;
use Shy\Http\Contract\Response;
use Shy\Http\Contract\View;
use Throwable;

class HandlerRegister
{
    /**
     * @var ExceptionHandler
     */
    protected $exceptionHandler;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var View
     */
    protected $view;

    /**
     * Set Exception Handler
     *
     * @param ExceptionHandler $exceptionHandler
     * @param Config $config
     * @param Logger $logger
     * @param Response|null $response
     * @param View|null $view
     *
     * @throws Cache\InvalidArgumentException
     */
    public function __construct(ExceptionHandler $exceptionHandler, Config $config, Logger $logger, Response $response = null, View $view = null)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->config = $config;
        $this->logger = $logger;
        $this->response = $response;
        $this->view = $view;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        if (!$config->find('app.debug')) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * 设置响应
     * Set response
     *
     * @param Response|null $response
     * @return $this
     */
    public function setResponse(Response $response = null)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * 设置视图
     * Set view
     *
     * @param View|null $view
     * @return $this
     */
    public function setView(View $view = null)
    {
        $this->view = $view;

        return $this;
    }

    /**
     * 将错误转换为异常
     * Convert PHP errors to ErrorException instances.
     *
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, string $file = '', int $line = 0, array $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * 处理未捕获异常
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param Throwable $e
     * @return void
     */
    public function handleException($e)
    {
        if (!$e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        try {
            $this->exceptionHandler->set($e);
            $this->exceptionHandler->logging($this->logger);
            $this->exceptionHandler->report();
            $this->exceptionHandler->response($this->config, $this->response, $this->view);
        } catch (Throwable $e) {
            echo implode(PHP_EOL, get_throwable_array($e));
        }
    }

    /**
     * 处理php shutdown事件
     * Handle the PHP shutdown event.
     */
    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * 从错误数组中创建新的致命异常
     * Create a new fatal exception instance from an error array.
     *
     * @param array $error
     * @param null $traceOffset
     * @return FatalErrorException
     *
     * @throws ReflectionException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }

    /**
     * 判断错误类型是否致命
     * Determine if the error type is fatal.
     *
     * @param int $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }
}
