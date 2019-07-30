<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\Logger as LoggerContract;

class Logger extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(LoggerContract::class);
    }
}
