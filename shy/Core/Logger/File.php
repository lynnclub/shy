<?php

namespace Shy\Core\Logger;

use Psr\Log\AbstractLogger;
use Shy\Core\Contracts\Logger as LoggerContract;
use Shy\Http\Contracts\Request;
use Shy\Core\Contracts\Config;

class File extends AbstractLogger implements LoggerContract
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
     * Set request
     *
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @throws \Exception
     */
    public function log($level, $message, array $context = array())
    {
        $prefix = '[' . date('Y-m-d H:i:s') . '] [' . $level . '] ';
        if (is_object($this->request) && $this->request->isInitialized()) {
            $prefix .= '[' . implode(',', $this->request->getClientIps()) . ' ' . $this->request->getUrl() . '] ';
        }

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

        @file_put_contents($filename, $prefix . ' ' . $message . ' ' . implode(PHP_EOL, $context) . PHP_EOL, FILE_APPEND);
    }

}
