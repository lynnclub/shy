<?php

namespace shy\core\facade;

use shy\core\facade;

class mysqli extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('shy_mysqli', 'shy\core\library\mysqli');
    }
}
