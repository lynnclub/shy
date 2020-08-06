<?php

namespace Shy\Core\DataBase;

use Pdo as PhpPdo;
use Shy\Core\Contracts\DataBase;
use Exception;

class Pdo extends PhpPdo implements DataBase
{
    /**
     * @var $this []
     */
    protected $connections;

    /**
     * Pdo constructor.
     *
     * @param string $config_name
     *
     * @throws Exception
     */
    public function __construct($config_name = 'default')
    {
        $this->connection($config_name);

        if ($config_name === 'default') {
            $this->connections[$config_name] = $this;
        }
    }

    /**
     * @param string $config_name
     *
     * @throws Exception
     *
     * @return $this
     */
    public function connection($config_name = 'default')
    {
        if (isset($this->connections[$config_name])) {
            return $this->connections[$config_name];
        }

        /**
         * If is Pdo Container
         */
        if ($config_name !== 'default') {
            $this->connections[$config_name] = new self($config_name);
            return $this->connections[$config_name];
        }

        $configs = config('database.db');
        if (!isset($configs[$config_name])) {
            throw new Exception(500, 'DataBase Config ' . $config_name . ' not set');
        }
        $config = $configs[$config_name];
        if (!isset($config['driver'], $config['host'], $config['database'], $config['username'], $config['password'])) {
            throw new Exception('DataBase Config ' . $config_name . ' error');
        }

        $dsn = $config['driver'] . ':host=' . $config['host'] . ';dbname=' . $config['database'];
        if (isset($config['port'])) {
            $dsn .= ';port=' . $config['port'];
        }

        parent::__construct(
            $dsn,
            $config['username'],
            $config['password'],
            [PDO::ATTR_PERSISTENT => TRUE]
        );

        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this;
    }

}
