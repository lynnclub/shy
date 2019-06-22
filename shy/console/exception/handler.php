<?php

namespace shy\console\exception;

use shy\core\exception\handler as handlerInterface;
use Exception;

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

        logger('exception: ' . $this->getErrorString($e), 'ERROR');

        echo
            PHP_EOL . $e->getMessage() . PHP_EOL .
            shy('console')->exceptionNotice() . PHP_EOL . PHP_EOL;
    }

    private function getErrorString(Exception $e)
    {
        return
            PHP_EOL . 'Message: ' . $e->getMessage() . '. Error Code: ' . $e->getCode()
            . PHP_EOL . PHP_EOL . 'File: ' . $e->getFile() . ' Line: ' . $e->getLine()
            . PHP_EOL . $e->getTraceAsString()
            . PHP_EOL . PHP_EOL;
    }
}
