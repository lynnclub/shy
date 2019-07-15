<?php
/**
 * Exception register
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

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
    public function setExceptionHandler(handler $handler = null)
    {
        $this->handler = $handler;

        error_reporting(-1);
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);

        if (config_key('debug')) {
            ini_set('display_errors', 'On');
        } else {
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
     * @return void
     *
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0)
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

        $traceString = $this->getTraceString($e);
        logger('exception', $traceString, 'ERROR');

        try {
            if (isset($this->handler)) {
                $this->handler->run($e);
            } else {
                echo config('IS_CLI') ? $traceString : nl2br($traceString);
            }
        } catch (Throwable $e) {
            echo config('IS_CLI') ? $traceString : nl2br($traceString);
        }
    }

    /**
     * Get trace string
     *
     * @param Throwable $e
     * @return string
     */
    protected function getTraceString(Throwable $e)
    {
        $traceString = '';
        foreach ($e->getTrace() as $key => $trace) {
            $traceString .= '[' . $key . '] ';

            if (isset($trace['file'], $trace['line'])) {
                $traceString .= $trace['file'] . ' ' . $trace['line'] . PHP_EOL;
            } else {
                $traceString .= 'none' . PHP_EOL;
            }

            if (isset($trace['class'])) {
                $traceString .= $trace['class'] . '->';
            }
            if (isset($trace['args'])) {
                foreach ($trace['args'] as $argKey => $arg) {
                    if (is_object($arg)) {
                        $trace['args'][$argKey] = '(object)' . get_class($arg);
                    } elseif (is_array($arg)) {
                        $trace['args'][$argKey] = '(array)' . json_encode($arg);
                    }
                }
            } else {
                $trace['args'] = [];
            }
            $traceString .= $trace['function'] . '(' . implode(', ', $trace['args']) . ')';
        }

        return PHP_EOL . 'Message: ' . $e->getMessage() . PHP_EOL .
            'File: ' . $e->getFile() . PHP_EOL .
            'Line: ' . $e->getLine() . PHP_EOL .
            'Error Code: ' . $e->getCode() . PHP_EOL .
            'Trace: ' . PHP_EOL .
            $traceString . PHP_EOL;
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
