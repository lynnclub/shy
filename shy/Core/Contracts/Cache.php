<?php

namespace Shy\Core\Contracts;

use ArrayAccess;

interface Cache extends ArrayAccess
{
    /**
     * Connection
     *
     * @param string $config_name
     * @return mixed
     */
    public function connection($config_name = 'default');
}
