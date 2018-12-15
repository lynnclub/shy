<?php

namespace shy\core;

use shy\core\exception\handler;
use Exception;
use Throwable;
use ErrorException;
use shy\core\exception\fatalThrowableError;
use shy\core\exception\fatalErrorException;

trait exceptionHandlerRegister
{
    private $handler;

    /**
     * Initialize
     *
     * @param handler $handler
     */
    public function setExceptionHandler(handler $handler)
    {
        $this->handler = $handler;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        if (config('env') === 'production') {
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
     * @throws
     */
    public function handleException($e)
    {
        if (!$e instanceof Exception) {
            $e = new fatalThrowableError($e);
        }
        try {
            $this->handler->run($e);
        } catch (Throwable $e) {
            echo 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . PHP_EOL .
                'Message:' . $e->getMessage() . ' Error Code: ' . $e->getCode() . PHP_EOL .
                $e->getTraceAsString() . PHP_EOL;
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
     * @throws
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
