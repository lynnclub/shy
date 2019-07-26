<?php

namespace Shy\Core;

use Psr\Log\AbstractLogger;
use Shy\Core\Contracts\Logger as LoggerContract;
use Shy\Http\Contracts\Request;
use Shy\Core\Contracts\Config;

class Logger extends AbstractLogger implements LoggerContract
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Request
     */
    protected $request;

    /**
     * Logger constructor.
     *
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Config $config, Request $request = null)
    {
        $this->config = $config;

        $this->request = $request;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @throws Exceptions\Cache\InvalidArgumentException
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        $prefix = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ';
        if (isset($this->request) && $this->request->isInitialized()) {
            $prefix .= '[' . implode(',', $this->request->getClientIps()) . ' ' . $this->request->getUrl() . '] ';
        }

        $context = implode(PHP_EOL, $context);

        /**
         * File and path
         */
        if (is_cli()) {
            $path = 'console/';
        } else {
            $path = 'web/';
        }
        $filename = CACHE_PATH . 'log/' . $path . date('Y-m-d') . '.log';
        if (!is_dir(dirname($filename))) {
            @mkdir(dirname($filename));
        }

        @file_put_contents($filename, $prefix . ' ' . $message . ' ' . $context . PHP_EOL, FILE_APPEND);

        /**
         * Additional logger
         */
        $add_log_function = $this->config->find('add_log_function');
        if (!empty($add_log_function) && function_exists($add_log_function)) {
            $add_log_function($level, $message, $context);
        }
    }

}
