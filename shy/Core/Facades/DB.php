<?php

namespace Shy\Core\Facades;

use Shy\Core\Facade;
use Shy\Core\Contract\DataBase;

/**
 * Class DB
 * @package Shy\Core\Facades
 *
 * @method static DB connection($config_name = 'default')
 * @method static DB table($config_name = 'default')
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
