<?php

namespace Shy\Http\Exceptions;

use Shy\Core\Contracts\ExceptionHandler;
use Throwable;
use Psr\Log\LoggerInterface;
use Shy\Http\Contracts\Response;

class Handler implements ExceptionHandler
{
    /**
     * @var Throwable
     */
    protected $throwable;

    /**
     * Set Throwable
     *
     * @param Throwable $throwable
     */
    public function set(Throwable $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * Get trace array
     *
     * @return array
     */
    protected function getTraceArray()
    {
        $array[] = 'Message: ' . $this->throwable->getMessage();
        $array[] = 'File: ' . $this->throwable->getFile();
        $array[] = 'Line: ' . $this->throwable->getLine();
        $array[] = 'Error Code: ' . $this->throwable->getCode();
        $array[] = 'Trace: ';

        foreach ($this->throwable->getTrace() as $key => $trace) {
            $traceString = '[' . $key . '] ';
            if (isset($trace['file'], $trace['line'])) {
                $traceString .= $trace['file'] . ' ' . $trace['line'];
            } else {
                $traceString .= 'none';
            }

            $array[] = $traceString;

            $traceString = '';
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

            $array[] = $traceString . $trace['function'] . '(' . implode(', ', $trace['args']) . ')';
        }

        return $array;
    }

    /**
     * Logging.
     *
     * @param LoggerInterface $logger
     */
    public function logging(LoggerInterface $logger)
    {
        $logger->error('Exception', $this->getTraceArray());
    }

    /**
     * Report.
     */
    public function report()
    {
        if (method_exists($this->throwable, 'report')) {
            $this->throwable->report();
        }
    }

    /**
     * Response.
     *
     * @param Response $response
     */
    public function response(Response $response)
    {
        if ($this->throwable instanceof HttpException) {
            $response->setCode($this->throwable->getStatusCode())->setHeader($this->throwable->getHeaders());

            if ($this->throwable->getStatusCode() === 404) {
                $response->set(view('errors/404'));
            } else {
                $response->set(view('errors/common', ['e' => $this->throwable]));
            }

            $response->send();
        } elseif (config_key('debug')) {
            $response->setCode(500)
                ->setHeader([])
                ->set(view('errors/exception', ['e' => $this->throwable]))
                ->send();
        }
    }

}
