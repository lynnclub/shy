<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contracts\Session as SessionContracts;

class Session extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(SessionContracts::class);
    }
}
