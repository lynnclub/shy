<?php

namespace Shy\Console\Command;

use Shy\Console;
use RuntimeException;
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
    public function list()
    {
        $list = shy(Console::class)->getCommandList();
        return implode(PHP_EOL, $list);
    }

    /**
     * Get Version
     *
     * @return string
     */
    public function version()
    {
        return 'Shy Framework v0.1' .
            PHP_EOL . 'The mini framework' .
            PHP_EOL . '( *^_^* )';
    }

    /**
     * Clear router cache
     *
     * @return string
     */
    public function clearRouterCache()
    {
        $routerCache = CACHE_PATH . 'app/router.cache';
        if (!file_exists($routerCache) || unlink($routerCache)) {
            return 'Success';
        } else {
            return 'Failed';
        }
    }

    /**
     * WorkerMan http service
     *
     * @throws \Exception
     */
    public function http()
    {
        $config = config_key('http', 'workerman');
        if (!isset($config['port'], $config['worker']) || !is_int($config['port']) || !is_int($config['worker'])) {
            throw new RuntimeException('WorkerMan setting error.');
        }

        global $argv;
        if (isset($argv[1])) {
            $argv[2] = $argv[1];
        }
        if (isset($argv[0])) {
            $argv[1] = $argv[0];
        }
        $web = shy(HttpInWorkerMan::class, null, 'http://0.0.0.0:' . $config['port']);
        if (!$web instanceof HttpInWorkerMan) {
            throw new \Exception('Class Worker not found.');
        }

        $web->count = $config['worker'];
        $web->addRoot('localhost', config_key('public', 'path'));

        Worker::$stdoutFile = config_key('cache', 'path') . 'log' . DIRECTORY_SEPARATOR . 'console' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
        Worker::runAll();
    }

    /**
     * WorkerMan socket
     */
    public function workerMan()
    {
        global $argv;
        if (!isset($argv[0])) {
            throw new RuntimeException('WorkerMan socket config not specified.');
        }
        $config = config_key('socket', 'workerman');
        if (isset($config[$argv[0]])) {
            $config = $config[$argv[0]];
        } else {
            throw new RuntimeException('WorkerMan socket config ' . $argv[0] . ' not found.');
        }
        if (!isset($config['address'], $config['worker'], $config['onConnect'], $config['onMessage'], $config['onClose']) || !is_int($config['worker'])) {
            throw new RuntimeException('WorkerMan socket setting error');
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
