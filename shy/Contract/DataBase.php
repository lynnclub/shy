<?php

namespace Shy\Contract;

interface DataBase
{
    /**
     * Connection
     *
     * @param string $config_name
     * @return object
     */
    public function connection(string $config_name = 'default');
}
