<?php

namespace Shy\DataBase;

use Illuminate\Database\Capsule\Manager;
use Exception;

class Illuminate extends Manager
{
    /**
     * Illuminate constructor.
     *
     * @throws Exception
     */
    public function __construct()
    {
        parent::__construct();

        $configs = config('database');
        foreach ($configs as $name => $item) {
            if (!isset($item['driver'], $item['host'], $item['database'], $item['username'], $item['password'])) {
                throw new Exception('Database config error.');
            }

            $this->addConnection($item, $name);
        }

        parent::setAsGlobal();
        parent::bootEloquent();
    }
}
