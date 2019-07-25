<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Session as realSession;

class Session extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(realSession::class);
    }
}
