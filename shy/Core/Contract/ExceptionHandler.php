<?php

namespace Shy\Core\Contract;

use Throwable;
use Shy\Http\Contract\Response;
use Shy\Http\Contract\View;

interface ExceptionHandler
{
    /**
     * Set Throwable
     *
     * @param Throwable $throwable
     */
    public function set(Throwable $throwable);

    /**
     * Logging.
     *
     * @param Logger $logger
     */
    public function logging(Logger $logger);

    /**
     * Report.
     */
    public function report();

    /**
     * Response.
     *
     * @param Config $config
     * @param Response $response
     * @param View $view
     */
    public function response(Config $config = null, Response $response = null, View $view = null);

}
