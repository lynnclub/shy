<?php

namespace Shy\Socket\Contract;

use Workerman\Connection\ConnectionInterface as Connection;

interface WorkerMan
{
    /**
     * On Connect
     *
     * @param Connection $connection
     * @return mixed
     */
    public function onConnect(Connection $connection);

    /**
     * On Message
     *
     * @param Connection $connection
     * @param $data
     * @return mixed
     */
    public function onMessage(Connection $connection, $data);

    /**
     * On Close
     *
     * @param Connection $connection
     * @return mixed
     */
    public function onClose(Connection $connection);
}
