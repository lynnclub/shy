<?php

namespace shy\http\exception;

use shy\exception\handler as handlerInterface;
use Exception;
use shy\http\facade\response;
use shy\http\facade\request;

class handler implements handlerInterface
{
    /**
     * Run handler
     *
     * @param Exception $e
     * @throws Exception
     */
    public function run(Exception $e)
    {
        $this->report($e);
        $this->response($e);
    }

    /**
     * Report or log an exception.
     *
     * @param Exception $e
     * @return mixed
     * @throws Exception
     */
    public function report(Exception $e)
    {
        if (method_exists($e, 'report')) {
            return $e->report();
        }

        logger('exception/', $this->getErrorString($e));
    }

    private function getErrorString(Exception $e)
    {
        return 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . PHP_EOL .
            'Message:' . $e->getMessage() . ' Error Code: ' . $e->getCode() . PHP_EOL .
            $e->getTraceAsString() . PHP_EOL;
    }

    /**
     * Response an exception.
     *
     * @param Exception $e
     * @return mixed
     */
    public function response(Exception $e)
    {
        if (method_exists($e, 'getStatusCode') && method_exists($e, 'getHeaders')) {
            response::setCode($e->getStatusCode())->setHeader($e->getHeaders());
            if ($e->getStatusCode() === 404) {
                return response::set(view('errors/404'))->send();
            }
        }

        request::expectsJson()
            ? response::set($this->getErrorString($e))
            : response::set(view('errors/exception', compact('e')));

        response::send();
    }
}
