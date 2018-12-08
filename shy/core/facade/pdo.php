<?php

namespace shy\core\facade;

use shy\core\facade;

class pdo extends facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy('shy_pdo', 'shy\core\library\pdo');
    }
}
