<?php
/**
 * 核心函数
 * Core function
 */

use Psr\Http\Message\StreamInterface;
use Shy\Container;
use Shy\Contract\Config;
use Shy\Library\Stream;

if (!function_exists('get_throwable_array')) {
    /**
     * 以数组形式获取可抛出对象
     * Get throwable array
     *
     * @param Throwable $throwable
     * @return array
     */
    function get_throwable_array(\Throwable $throwable)
    {
        $array = [
            'Message: ' . $throwable->getMessage(),
            'Error Code: ' . $throwable->getCode(),
            'File: ' . $throwable->getFile() . ' line ' . $throwable->getLine(),
            '',
            'Trace: ',
        ];

        foreach ($throwable->getTrace() as $key => $trace) {
            $argString = '';
            if (isset($trace['args'])) {
                foreach ($trace['args'] as $argKey => $arg) {
                    if (is_object($arg)) {
                        $trace['args'][$argKey] = '(object)' . get_class($arg);
                    } elseif (is_array($arg)) {
                        $trace['args'][$argKey] = '(array)' . json_encode($arg, JSON_UNESCAPED_UNICODE);
                    }
                }

                $argString = implode(', ', $trace['args']);
            }

            // call
            $traceString = '[' . $key . '] ';
            if (isset($trace['class'])) {
                $traceString .= $trace['class'] . '->';
            }
            $array[] = $traceString . $trace['function'] . '(' . $argString . ')' . PHP_EOL;

            // file
            $traceString = '';
            if (isset($trace['file'], $trace['line'])) {
                $traceString .= $trace['file'] . ' line ' . $trace['line'];
            } else {
                $traceString .= '{anonymous}';
            }
            $array[] = $traceString;
        }

        return $array;
    }
}

if (!function_exists('shy')) {
    /**
     * 获取或创建实例
     * Get or make instance
     *
     * @param string|null $id
     * @param object|string|null $concrete
     * @param ...$parameters
     *
     * @return object
     */
    function shy(string $id = null, $concrete = null, ...$parameters)
    {
        if (is_null($id)) {
            return Container::getContainer();
        }

        return Container::getContainer()->getOrMake($id, $concrete, ...$parameters);
    }
}

if (!function_exists('bind')) {
    /**
     * 绑定待创建
     * Bind ready to make
     *
     * @param string $id
     * @param string|Closure|object|null $concrete
     *
     * @return Container
     */
    function bind(string $id, $concrete = null)
    {
        return Container::getContainer()->bind($id, $concrete);
    }
}

if (!function_exists('config')) {
    /**
     * 获取配置
     * Get config
     *
     * @param string|null $key
     * @return mixed
     */
    function config(string $key = null)
    {
        if (is_null($key)) {
            return shy(Config::class);
        }

        return shy(Config::class)->find($key);
    }
}

if (!function_exists('lang')) {
    /**
     * 多语言
     * lang
     *
     * @param int $code
     * @param string $language
     * @return string
     */
    function lang(int $code, string $language = '')
    {
        if (empty($language)) {
            $language = config('app.default_lang');
        }

        return config('lang/' . $language . '.' . $code);
    }
}

if (!function_exists('is_cli')) {
    /**
     * 是否运行在cli模式
     * Determine if running in cli.
     *
     * @return bool
     */
    function is_cli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}

if (!function_exists('stream_for')) {
    /**
     * 基于输入类型创建stream流
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * @param mixed $resource
     * @param array $options Additional options
     *
     * @return StreamInterface
     * @throws InvalidArgumentException if the $resource arg is not valid.
     */
    function stream_for($resource = '', array $options = [])
    {
        if (is_scalar($resource)) {
            $stream = fopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }

            return new Stream($stream, $options);
        }

        switch (gettype($resource)) {
            case 'resource':
                return new Stream($resource, $options);
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif (method_exists($resource, '__toString')) {
                    return stream_for((string)$resource, $options);
                } else {
                    return stream_for(json_encode($resource), $options);
                }
            case 'array':
                return stream_for(json_encode($resource), $options);
            case 'NULL':
                return new Stream(fopen('php://temp', 'r+'), $options);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . gettype($resource));
    }
}
