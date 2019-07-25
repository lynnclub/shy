<?php

namespace Shy\Core\Exceptions;

use Shy\Core\Contracts\ExceptionHandler;
use Shy\Core\Contracts\Logger;
use Shy\Http\Contracts\Response;
use Exception;
use Throwable;
use ErrorException;

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
     * Set Exception Handler
     *
     * @param ExceptionHandler $exceptionHandler
     * @param Logger $logger
     * @param Response $response
     * @param bool $debug
     */
    public function __construct(ExceptionHandler $exceptionHandler, Logger $logger, Response $response, $debug = false)
    {
        $this->exceptionHandler = $exceptionHandler;
        $this->logger = $logger;
        $this->response = $response;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        if (!$debug) {
            ini_set('display_errors', 'Off');
        }
    }

    /**
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
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if (error_reporting() & $level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param Throwable $e
     * @return void
     *
     * @throws \ReflectionException
     */
    public function handleException($e)
    {
        if (!$e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        $this->exceptionHandler->set($e);

        try {
            $this->exceptionHandler->logging($this->logger);
        } catch (Throwable $e) {
            //
        }

        try {
            $this->exceptionHandler->report();
        } catch (Throwable $e) {
            //
        }

        try {
            $this->exceptionHandler->response($this->response);
        } catch (Throwable $e) {
            //
        }
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @throws \ReflectionException
     */
    public function handleShutdown()
    {
        if (!is_null($error = error_get_last()) && $this->isFatal($error['type'])) {
            $this->handleException($this->fatalExceptionFromError($error, 0));
        }
    }

    /**
     * Create a new fatal exception instance from an error array.
     *
     * @param array $error
     * @param null $traceOffset
     * @return FatalErrorException
     * @throws
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new FatalErrorException(
            $error['message'], $error['type'], 0, $error['file'], $error['line'], $traceOffset
        );
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
    }

}
