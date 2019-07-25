<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contracts\Response as ResponseContracts;

class Response extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(ResponseContracts::class);
    }
}
