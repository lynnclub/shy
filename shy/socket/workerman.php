<?php
/**
 * WorkerMan Socket Interface
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\socket;

use Workerman\Connection\ConnectionInterface as Connection;

interface workerMan
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
