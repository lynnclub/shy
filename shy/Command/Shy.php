<?php

namespace Shy\Command;

use Shy\SocketInWorkerMan;
use Shy\HttpInWorkerMan;
use Workerman\Worker;

class Shy
{
    /**
     * The List of Command
     *
     * @return string
     */
    public function commandList()
    {
        $list = array_keys(config('command'));
        asort($list);

        return implode(PHP_EOL, $list);
    }

    /**
     * SHY_ENV
     *
     * @return mixed
     */
    public function env()
    {
        return defined('SHY_ENV') ? SHY_ENV : 'Not defined';
    }

    /**
     * Show route index
     *
     * @return string
     */
    public function showRouteIndex()
    {
        bind(\Shy\Http\Contracts\Request::class, \Shy\Http\Request::class);

        $router = shy(\Shy\Http\Contracts\Router::class, \Shy\Http\Router::class);
        $router->initialize();
        $router->buildRouteIndexByConfig();

        return json_encode($router->getRouteIndex());
    }

    /**
     * WorkerMan http service
     *
     * @throws \Exception
     */
    public function httpWorkerMan()
    {
        $config = config('workerman.http');
        if (!isset($config['port'], $config['worker']) || !is_int($config['port']) || !is_int($config['worker'])) {
            return 'WorkerMan http setting error in `config/*/workerman.php`';
        }

        global $argv;
        if (isset($argv[1])) {
            $argv[2] = $argv[1];
        }
        if (isset($argv[0])) {
            $argv[1] = $argv[0];
            $argv[0] = 'command http_workerman';
        } else {
            $argv[0] = '';
        }

        $web = shy(HttpInWorkerMan::class, null, 'http://0.0.0.0:' . $config['port']);
        $web->count = $config['worker'];
        $web->addRoot('localhost', PUBLIC_PATH);

        Worker::$stdoutFile = CACHE_PATH . 'log/command/' . date('Ymd') . '.log';
        Worker::runAll();
    }

    /**
     * WorkerMan socket
     */
    public function workerMan()
    {
        global $argv;
        if (!isset($argv[0])) {
            return 'WorkerMan socket config not found in `config/*/workerman.php`';
        }

        $config = config('workerman.socket');
        if (isset($config[$argv[0]])) {
            $config = $config[$argv[0]];
            $argv[0] = 'command workerman ' . $argv[0];
        } else {
            return 'WorkerMan socket config `' . $argv[0] . '` not found in `config/*/workerman.php`';
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
