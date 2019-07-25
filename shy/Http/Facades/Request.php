<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contracts\Request as RequestContracts;

class Request extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(RequestContracts::class);
    }
}
