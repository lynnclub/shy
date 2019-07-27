<?php

namespace Shy\Core\DataBase;

use Illuminate\Database\Capsule\Manager;
use Exception;

class Illuminate extends Manager
{
    /**
     * Pdo constructor.
     *
     * @param string $config_name
     *
     * @throws Exception
     */
    public function __construct($config_name = 'default')
    {
        parent::__construct();
        parent::setAsGlobal();

        $configs = config_key('db', 'database');
        foreach ($configs as $name => $item) {
            if (!isset($item['driver'], $item['host'], $item['database'], $item['username'], $item['password'])) {
                throw new Exception('Database config error.');
            }

            $this->addConnection($item, $name);
        }
    }

}
