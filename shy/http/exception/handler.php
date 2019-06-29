<?php
/**
 * Http exception handler
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http\exception;

use shy\core\exception\handler as handlerInterface;
use Exception;
use shy\http\response;

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
    }

    /**
     * Response an exception.
     *
     * @param Exception $e
     * @throws
     * @return bool
     */
    public function response(Exception $e)
    {
        if (!config_key('debug')) {
            return false;
        }

        $response = shy(response::class);
        if (method_exists($e, 'getStatusCode') && method_exists($e, 'getHeaders')) {
            $response->setCode($e->getStatusCode())->setHeader($e->getHeaders());
            if ($e->getStatusCode() === 404) {
                return $response->set(view('errors/404'))->send();
            }
        } else {
            $response->setCode(500);
        }

        $response->set(view('errors/exception', compact('e')))->send();
    }

}
