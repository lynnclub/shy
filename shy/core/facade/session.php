<?php

namespace shy\core\facade;

use shy\core\facade;

class session extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('session', 'shy\core\library\session');
    }
}
