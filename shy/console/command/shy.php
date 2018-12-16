<?php

/**
 * Shy command
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\console\command;

use RuntimeException;
use shy\webInWorkerMan;
use Workerman\Worker;

class shy
{
    public function list()
    {
        $list = shy('console')->getCommandList();
        return implode(PHP_EOL, $list);
    }

    public function version()
    {
        return 'Shy Framework v0.1' .
            PHP_EOL . 'The mini framework' .
            PHP_EOL . '( *^_^* )';
    }


    public function http()
    {
        $config = config('worker_man_http');
        if (!isset($config['port'], $config['worker']) || !is_int($config['port']) || !is_int($config['worker'])) {
            throw new RuntimeException('WorkerMan setting error');
        }

        global $argv;
        if (isset($argv[1])) {
            $argv[2] = $argv[1];
        }
        if (isset($argv[0])) {
            $argv[1] = $argv[0];
        }
        $web = new webInWorkerMan('http://0.0.0.0:' . $config['port']);
        $web->count = $config['worker'];
        $web->addRoot('localhost', config('public', 'path'));

        Worker::$stdoutFile = config('cache', 'path') . 'log' . DIRECTORY_SEPARATOR . 'workerman' . DIRECTORY_SEPARATOR . 'http-' . date('Y-m-d') . '.log';
        Worker::runAll();
    }

    public function workerman()
    {
        $config = config('worker_man_socket');
        global $argv;
        if (isset($config[$argv[1]])) {
            $config = $config[$argv[1]];
        } else {
            throw new RuntimeException('WorkerMan socket config ' . $argv[1] . ' not found.');
        }
        if (!isset($config['address'], $config['worker'], $config['onConnect'], $config['onMessage'], $config['onClose']) || !is_int($config['worker'])) {
            throw new RuntimeException('WorkerMan setting error');
        }

        if (isset($argv[0])) {
            $argv[1] = $argv[0];
        }
        $worker = new Worker($config['address']);
        $worker->count = $config['worker'];

        if (!empty($config['onConnect'])) {
            $worker->onConnect = function ($connection) {
                shy('pipeline');
            };
        }

        if (!empty($config['onMessage'])) {
            $worker->onMessage = function ($connection, $data) {
                shy('pipeline');
            };
        }

        if (!empty($config['onClose'])) {
            $worker->onClose = function ($connection) {
                shy('pipeline');
            };
        }

        Worker::$stdoutFile = config('cache', 'path') . 'log' . DIRECTORY_SEPARATOR . 'workerman' . DIRECTORY_SEPARATOR . 'socket-' . date('Y-m-d') . '.log';
        Worker::runAll();
    }

}
