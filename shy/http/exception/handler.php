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
     */
    public function response(Exception $e)
    {
        $response = shy(response::class);

        if ($e instanceof httpException) {
            $response->setCode($e->getStatusCode())->setHeader($e->getHeaders());

            if ($e->getStatusCode() === 404) {
                $response->set(view('errors/404'));
            } else {
                $response->set(view('errors/common', compact('e')));
            }

            $response->send();
        } elseif (config_key('debug')) {
            $response->setCode(500)
                ->setHeader([])
                ->set(view('errors/exception', compact('e')))
                ->send();
        }
    }

}
