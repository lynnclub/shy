<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;

class Smarty extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('smarty');
    }
}
