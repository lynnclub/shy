<?php

namespace Shy\Core\DataBase;

use Illuminate\Database\Capsule\Manager;
use Exception;

class Illuminate extends Manager
{
    /**
     * Pdo constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();
        parent::setAsGlobal();

        $configs = config('database.db');
        foreach ($configs as $name => $item) {
            if (!isset($item['driver'], $item['host'], $item['database'], $item['username'], $item['password'])) {
                throw new Exception('Database config error.');
            }

            $this->addConnection($item, $name);
        }
    }

}
