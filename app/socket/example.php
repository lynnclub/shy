<?php

/**
 * Example socket
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\socket;

use Workerman\Connection\ConnectionInterface as Connection;
use shy\socket\workerMan;

class example implements workerMan
{
    public function onConnect(Connection $connection)
    {
        $connection->send('On Connect');
    }

    public function onMessage(Connection $connection, $data)
    {
        $connection->send('On Message');
    }

    public function onClose(Connection $connection)
    {
        $connection->send('On Close');
    }

}
