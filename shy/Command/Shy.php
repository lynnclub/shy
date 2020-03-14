<?php

namespace Shy\Command;

use Shy\Command;
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
        $list = shy(Command::class)->getList();
        asort($list);
        return implode(PHP_EOL, $list);
    }

    /**
     * Get Version
     *
     * @return string
     */
    public function version()
    {
        return 'Shy Framework ' . shy()->version();
    }

    /**
     * Shy env
     *
     * @return mixed
     */
    public function env()
    {
        return defined('SHY_ENV') ? SHY_ENV : 'Not defined';
    }

    /**
     * Delete config cache file
     *
     * @return string
     */
    public function deleteConfigCacheFile()
    {
        $cache = CACHE_PATH . 'app/config.cache';
        if (!file_exists($cache)) {
            return 'No cache';
        }

        if (unlink($cache)) {
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
        $config = config('workerman.http');
        if (!isset($config['port'], $config['worker']) || !is_int($config['port']) || !is_int($config['worker'])) {
            throw new RuntimeException('WorkerMan http setting error.');
        }

        global $argv;
        if (isset($argv[1])) {
            $argv[2] = $argv[1];
        }
        if (isset($argv[0])) {
            $argv[1] = $argv[0];
        }

        $web = shy(HttpInWorkerMan::class, null, 'http://0.0.0.0:' . $config['port']);
        $web->count = $config['worker'];
        $web->addRoot('localhost', config('path.public'));

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
            throw new RuntimeException('WorkerMan socket config not specified.');
        }

        $config = config('workerman.socket');
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
