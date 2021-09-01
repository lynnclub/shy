<?php

namespace Shy\Core\Contracts;

interface DataBase
{
    /**
     * Connection
     *
     * @param string $config_name
     * @return object
     */
    public function connection($config_name = 'default');
}
