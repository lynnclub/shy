<?php

namespace Shy\Core\Contracts;

interface DataBase
{
    /**
     * @param string $config_name
     */
    public function connection($config_name = 'default');

}
