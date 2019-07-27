<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\DataBase;

class DB extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(DataBase::class);
    }
}
