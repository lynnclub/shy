<?php

namespace Shy\Core\Contracts;

use Throwable;
use Psr\Log\LoggerInterface;
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
     * @param LoggerInterface $logger
     */
    public function logging(LoggerInterface $logger);

    /**
     * Report.
     */
    public function report();

    /**
     * Response.
     *
     * @param Response $response
     */
    public function response(Response $response);

}
