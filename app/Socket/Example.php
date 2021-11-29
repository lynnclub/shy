<?php
/**
 * Example socket
 */

namespace App\Socket;

use Workerman\Connection\ConnectionInterface as Connection;
use Shy\Socket\Contract\WorkerMan;

class Example implements WorkerMan
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
