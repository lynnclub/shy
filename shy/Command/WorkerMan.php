<?php

namespace Shy\Command;

use Exception;
use Shy\HttpInWorkerMan;
use Shy\SocketInWorkerMan;
use Workerman\Worker;

class WorkerMan
{
    /**
     * Http服务
     * WorkerMan http service
     *
     * @throws Exception
     */
    public function httpWorkerMan()
    {
        $config = config('workerman.http');
        if (!isset($config['port'], $config['worker']) || !is_int($config['port']) || !is_int($config['worker'])) {
            return 'WorkerMan http setting error in `config/*/workerman.php`';
        }

        global $argv;
        $argv[0] = 'http_workerman';

        $web = shy(HttpInWorkerMan::class, null, 'http://0.0.0.0:' . $config['port']);
        $web->count = $config['worker'];
        $web->addRoot('localhost', PUBLIC_PATH);

        Worker::$processTitle = 'http_workerman';
        Worker::$pidFile = CACHE_PATH . 'app/http_workerman.pid';
        Worker::$logFile = CACHE_PATH . 'log/command/' . date('Ymd') . '.log';
        Worker::runAll();
    }

    /**
     * Socket服务
     * WorkerMan socket service
     */
    public function workerMan()
    {
        global $argv;
        if (empty($argv[1])) {
            return 'WorkerMan socket not specified';
        }

        $config = config('workerman.socket');
        if (isset($config[$argv[1]])) {
            $config = $config[$argv[1]];

            $argv[0] = 'workerman ' . $argv[1];
            $argv[1] = $argv[2];
            $argv[2] = $argv[3] ?? '';
        } else {
            return 'WorkerMan socket config `' . $argv[1] . '` not found in `config/*/workerman.php`';
        }

        if (!isset($config['address'], $config['worker'], $config['onConnect'], $config['onMessage'], $config['onClose']) || !is_int($config['worker'])) {
            return 'WorkerMan socket setting error';
        }

        $worker = shy(SocketInWorkerMan::class, null, $config['address']);
        $worker->count = $config['worker'];

        if (!empty($config['onConnect'])) {
            $onConnectClass = key($config['onConnect']);
            $onConnectMethod = current($config['onConnect']);
            $worker->onConnect = function ($connection) use ($onConnectClass, $onConnectMethod) {
                if (method_exists($onConnectClass, $onConnectMethod)) {
                    shy($onConnectClass)->$onConnectMethod($connection);
                }
            };
        }

        if (!empty($config['onMessage'])) {
            $onMessageClass = key($config['onMessage']);
            $onMessageMethod = current($config['onMessage']);
            $worker->onMessage = function ($connection, $data) use ($onMessageClass, $onMessageMethod) {
                if (method_exists($onMessageClass, $onMessageMethod)) {
                    shy($onMessageClass)->$onMessageMethod($connection, $data);
                }
            };
        }

        if (!empty($config['onClose'])) {
            $onCloseClass = key($config['onClose']);
            $onCloseMethod = current($config['onClose']);
            $worker->onClose = function ($connection) use ($onCloseClass, $onCloseMethod) {
                if (method_exists($onCloseClass, $onCloseMethod)) {
                    shy($onCloseClass)->$onCloseMethod($connection);
                }
            };
        }

        SocketInWorkerMan::runAll();
    }
}
