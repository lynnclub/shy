<?php

namespace Shy;

use Workerman\Worker;
use Workerman\Lib\Timer;

class SocketInWorkerMan extends Worker
{
    /**
     * Construct.
     *
     * @param string $socket_name
     * @param array $context_option
     */
    public function __construct($socket_name, $context_option = array())
    {
        parent::__construct($socket_name, $context_option);
    }

    /**
     * Customer Init.
     *
     * @return void
     */
    protected static function init()
    {
        set_error_handler(function ($code, $msg, $file, $line) {
            Worker::safeEcho("$msg in file $file on line $line\n");
        });

        // Start file.
        global $argv;
        $cachePath = config('path.cache');
        static::$_startFile = $cachePath . 'log/console/workerman_' . $argv[0] . '.log';

        $unique_prefix = str_replace('/', '_', static::$_startFile);

        // Pid file.
        static::$pidFile = $cachePath . "app/$unique_prefix.pid";

        // Log file.
        static::$logFile = static::$_startFile;
        $log_file = (string)static::$logFile;
        if (!is_file($log_file)) {
            touch($log_file);
            chmod($log_file, 0622);
        }

        // State.
        static::$_status = static::STATUS_STARTING;

        // For statistics.
        static::$_globalStatistics['start_timestamp'] = time();
        static::$_statisticsFile = sys_get_temp_dir() . "/$unique_prefix.status";

        // Process title.
        static::setProcessTitle('WorkerMan: master process  start_file=' . static::$_startFile);

        // Init data for worker id.
        static::initId();

        // Timer init.
        Timer::init();
    }

}
