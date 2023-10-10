<?php

namespace Shy\Logger;

use Exception;
use Psr\Log\AbstractLogger;
use Psr\Log\InvalidArgumentException;
use Psr\Log\LogLevel;
use Shy\Contract\Logger as LoggerContract;
use Shy\Http\Contract\Request;

class File extends AbstractLogger implements LoggerContract
{
    /**
     * 请求
     *
     * @var Request
     */
    protected $request;

    /**
     * 日志级别
     *
     * @var array
     */
    protected $levels;

    /**
     * Logger constructor.
     *
     * @param Request|null $request
     */
    public function __construct(Request $request = null)
    {
        $this->request = $request;

        $this->levels = [LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL, LogLevel::ERROR, LogLevel::WARNING, LogLevel::NOTICE, LogLevel::INFO, LogLevel::DEBUG];
    }

    /**
     * 设置请求
     * Set request
     *
     * @param Request|null $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * 记录日志
     * Log with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @throws Exception
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
        if (isset($this->request) && method_exists($this->request, 'isInitialized') && $this->request->isInitialized()) {
            $prefix .= ' [' . $this->request->getUri() . ']';

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
