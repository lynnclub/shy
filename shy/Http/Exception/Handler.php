<?php

namespace Shy\Http\Exception;

use Shy\Core\Contract\ExceptionHandler;
use Shy\Core\Exception\Cache\InvalidArgumentException;
use Throwable;
use Shy\Core\Contract\Logger;
use Shy\Core\Contract\Config;
use Shy\Http\Contract\Response;
use Shy\Http\Contract\View;

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
     * Logging.
     *
     * @param Logger $logger
     */
    public function logging(Logger $logger)
    {
        if ($this->throwable instanceof HttpException) {
            $logger->error('Http Code ' . $this->throwable->getStatusCode() . ': ', [$this->throwable->getMessage()]);
        } else {
            $logger->error('Exception', get_throwable_array($this->throwable));
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
            echo implode(PHP_EOL, get_throwable_array($this->throwable));
            return;
        }

        $view->initialize();

        if ($this->throwable instanceof HttpException) {
            $response->withStatus($this->throwable->getStatusCode())
                ->withHeaders($this->throwable->getHeaders());

            if ($this->throwable->getStatusCode() === 404) {
                $response->output($view->view('errors/404'));
            } else {
                $response->output($view->view('errors/common')->with(['e' => $this->throwable]));
            }
        } else {
            $response->withStatus(500)
                ->withoutHeader(null)
                ->output($config->find('app.debug') ? $view->view('errors/exception')->with(['e' => $this->throwable]) : '');
        }
    }
}
