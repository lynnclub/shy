<?php

/**
 * Shy command
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\console\command;

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

    public function workerman()
    {
        global $argv;
        $argv[1] = $argv[0];
        Worker::$stdoutFile = config('cache', 'path') . 'log' . DIRECTORY_SEPARATOR . 'workerman' . DIRECTORY_SEPARATOR . date('YmdHis') . '.log';

        $web = new webInWorkerMan('http://0.0.0.0:2348');

        $web->count = 1;

        $web->addRoot('localhost', config('public', 'path'));

        Worker::runAll();
    }

}
