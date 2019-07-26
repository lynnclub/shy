<?php

namespace Shy\Core\Contracts;

use Throwable;
use Shy\Http\Contracts\Response;

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
     * @param Response $response
     */
    public function response(Response $response = null);

}
