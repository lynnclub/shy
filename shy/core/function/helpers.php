<?php
/**
 * Helpers functions
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

if (!function_exists('init_illuminate_database')) {
    /**
     * Init illuminate database
     *
     * @return object Illuminate\Database\Capsule\Manager
     */
    function init_illuminate_database()
    {
        $capsule = shy(Illuminate\Database\Capsule\Manager::class);
        $database = config_key('db', 'database');
        if (is_array($database)) {
            $capsule->setAsGlobal();
            foreach ($database as $name => $item) {
                if (isset($item['driver'], $item['host'], $item['port'], $item['database'], $item['username'], $item['password'], $item['charset'], $item['collation'])) {
                    $capsule->addConnection([
                        'driver' => $item['driver'],
                        'host' => $item['host'],
                        'database' => $item['database'],
                        'username' => $item['username'],
                        'password' => $item['password'],
                        'charset' => $item['charset'],
                        'collation' => $item['collation'],
                        'prefix' => '',
                    ], $name);
                } else {
                    throw new RuntimeException('Database config error.');
                }
            }
            return $capsule;
        } else {
            throw new RuntimeException('Database config error.');
        }
    }
}

if (!function_exists('logger')) {
    /**
     * Log
     *
     * @param string $topic
     * @param string|array $msg
     * @param string $level
     * @param string $filename
     * @param string $datetimeFormat
     */
    function logger(string $topic, $msg, string $level = 'INFO', string $filename = '', string $datetimeFormat = 'Y-m-d')
    {
        if (empty($filename)) {
            if (config('IS_CLI')) {
                $filename = 'console/';
            } else {
                $filename = 'web/';
            }
        }
        if ($datetimeFormat) {
            $filename .= date($datetimeFormat);
        }
        $filename = config_key('cache', 'path') . 'log/' . $filename . '.log';
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename));
        }

        $prefix = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ';
        $request = shy(shy\http\request::class);
        if ($request->isInit()) {
            $prefix .= '[' . implode(',', $request->getClientIps()) . ' ' . $request->getUrl() . '] ';
        }

        if (!is_string($msg)) {
            $msg = json_encode($msg, JSON_UNESCAPED_UNICODE);
        }
        $msg = $topic . ' ' . $msg;

        @file_put_contents($filename, $prefix . $msg . PHP_EOL, FILE_APPEND);

        $add_log_function = config_key('add_log_function');
        if (!empty($add_log_function) && function_exists($add_log_function)) {
            $add_log_function($topic, $msg, $level, $filename, $datetimeFormat);
        }
    }
}

if (!function_exists('dd')) {
    /**
     * Development output
     *
     * @param mixed $msg
     */
    function dd(...$msg)
    {
        foreach ($msg as $item) {
            var_dump($item);
        }

        exit(0);
    }
}
