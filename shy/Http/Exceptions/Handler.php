<?php

namespace Shy\Http\Exceptions;

use Shy\Core\Contracts\ExceptionHandler;
use Shy\Core\Exceptions\Cache\InvalidArgumentException;
use Throwable;
use Shy\Core\Contracts\Logger;
use Shy\Core\Contracts\Config;
use Shy\Http\Contracts\Response;
use Shy\Http\Contracts\View;

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

            $array[] = $traceString . $trace['function'] . '(' . implode(', ', $trace['args']) . ')' . PHP_EOL;
        }

        return $array;
    }

    /**
     * Logging.
     *
     * @param Logger $logger
     */
    public function logging(Logger $logger)
    {
        if ($this->throwable instanceof HttpException) {
            $logger->error('Http Code ' . $this->throwable->getStatusCode() . ': ', [$this->throwable->getMessage()]);
        } else {
            $logger->error('Exception', $this->getTraceArray());
        }
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
     * @param Config $config
     * @param Response $response
     * @param View $view
     *
     * @throws InvalidArgumentException
     */
    public function response(Config $config = null, Response $response = null, View $view = null)
    {
        if (is_null($response) || is_null($view)) {
            echo implode(PHP_EOL, $this->getTraceArray());
            return;
        }

        $view->initialize();
        if ($this->throwable instanceof HttpException) {
            $response->setCode($this->throwable->getStatusCode())
                ->setHeader($this->throwable->getHeaders());

            if ($this->throwable->getStatusCode() === 404) {
                $response->set($view->view('errors/404'));
            } else {
                $response->set($view->view('errors/common')->with(['e' => $this->throwable]));
            }

            $response->send();
        } else {
            $response->setCode(500)
                ->setHeader([])
                ->set($config->find('app.debug') ? $view->view('errors/exception')->with(['e' => $this->throwable]) : '')
                ->send();
        }
    }

}
