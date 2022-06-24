<?php

namespace Shy\Facade;

use Shy\Facade;
use Shy\Contract\DataBase;

/**
 * Class DB
 * @package Shy\Facade
 *
 * @method static DB connection($config_name = 'default')
 * @method static DB table($name)
 */
class DB extends Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(DataBase::class);
    }
}
