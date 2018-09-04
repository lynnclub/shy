<?php

namespace shy\core;

use shy\exception\handler;
use Exception;
use Throwable;
use ErrorException;
use shy\exception\fatalThrowableError;
use shy\exception\fatalErrorException;

trait exceptionHandlerRegister
{
    private $handler;

    /**
     * Initialize
     *
     * @param handler $handler
     * @param string $env
     */
    public function setExceptionHandler(handler $handler, $env = '')
    {
        $this->handler = $handler;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        if ($env !== 'testing') {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * Convert PHP errors to ErrorException instances.
     *
     * @param  int $level
     * @param  string $message
     * @param  string $file
     * @param  int $line
     * @param  array $context
     * @return void
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
     * @param $e
     * @return void
     */
    public function handleException($e)
    {
        if (!$e instanceof Exception) {
            $e = new fatalThrowableError($e);
        }
        try {
            $this->handler->report($e);
            $this->handler->response($e);
        } catch (Throwable $e) {
            echo 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . "\r\n" .
                'Message:' . $e->getMessage() . ' Error Code: ' . $e->getCode() . "\r\n" .
                $e->getTraceAsString() . "\r\n";
        }
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
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
     * @return fatalErrorException
     */
    protected function fatalExceptionFromError(array $error, $traceOffset = null)
    {
        return new fatalErrorException(
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
