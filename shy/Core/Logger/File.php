<?php

namespace Shy\Core\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
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
     * @var array
     */
    protected $levels;

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

        $this->levels = [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG];
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
        if (!is_string($level)) {
            throw new InvalidArgumentException('Log level \'' . $level . '\' is not string.');
        }
        if (!in_array($level, $this->levels)) {
            throw new InvalidArgumentException('Log level \'' . $level . '\' not defined.');
        }
        if (!is_string($message)) {
            throw new InvalidArgumentException('Log message is not string.');
        }

        $path = is_cli() ? 'command/' : 'web/';
        $dir = CACHE_PATH . 'log/' . $path;
        if (!is_dir($dir)) {
            mkdir(dirname($dir));
        }

        $prefix = '[' . date('Y-m-d H:i:s') . '] [' . strtoupper($level) . ']';
        if (method_exists($this->request, 'isInitialized') && $this->request->isInitialized()) {
            $prefix .= ' [' . $this->request->getUrl() . ']';

            $userIps = $this->request->getClientIps();
            if (!empty($userIps)) {
                $prefix .= ' [' . implode(',', $userIps) . ']';
            }

            $prefix .= ' [' . $this->request->getMethod() . ']';
            $prefix .= ' [' . $this->request->header('User-Agent') . ']';
        }

        $contextString = '';
        foreach ($context as $key => $val) {
            if (!is_numeric($key)) {
                $contextString .= $key . ' ';
            }

            if (is_string($val)) {
                $contextString .= $val . PHP_EOL;
            } else {
                if (is_object($val) && method_exists($val, '__toString')) {
                    $contextString .= $val->__toString() . PHP_EOL;
                } else {
                    $contextString .= json_encode($val, JSON_UNESCAPED_UNICODE) . PHP_EOL;
                }
            }
        }

        file_put_contents($dir . date('Ymd') . '.log', $prefix . ' ' . $message . PHP_EOL . $contextString, FILE_APPEND);
    }
}
