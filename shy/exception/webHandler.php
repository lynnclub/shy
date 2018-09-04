<?php

namespace shy\exception;

use Exception;
use shy\facade\response;
use shy\facade\request;

class webHandler implements handler
{
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
        return 'File: ' . $e->getFile() . ' Line: ' . $e->getLine() . "\r\n" .
            'Message:' . $e->getMessage() . ' Error Code: ' . $e->getCode() . "\r\n" .
            $e->getTraceAsString() . "\r\n";
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

        return response::send();
    }
}
