<?php

namespace shy\core\exception;

use Exception;

interface handler
{
    /**
     * Run handler.
     *
     * @param Exception $e
     * @throws Exception
     */
    public function run(Exception $e);
}
