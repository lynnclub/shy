<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contracts\DataBase;

/**
 * Class DB
 * @package Shy\Core\Facades
 *
 * @method static connection($config_name = 'default')
 * @method static table($config_name = 'default')
 */
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
