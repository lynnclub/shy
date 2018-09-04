<?php

namespace shy\exception;

use Exception;

interface handler
{
    /**
     * Report or log an exception.
     *
     * @param Exception $e
     * @return mixed
     * @throws Exception
     */
    public function report(Exception $e);

    /**
     * Response an exception.
     *
     * @param Exception $e
     * @return mixed
     */
    public function response(Exception $e);
}
