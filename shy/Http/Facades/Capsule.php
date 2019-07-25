<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Illuminate\Database\Capsule\Manager;

class Capsule extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(Manager::class);
    }
}
