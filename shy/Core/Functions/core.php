<?php
/**
 * Core functions
 */

if (!function_exists('get_throwable_array')) {
    /**
     * Get throwable array
     *
     * @param Throwable $throwable
     * @return array
     */
    function get_throwable_array(Throwable $throwable)
    {
        $array[] = 'Message: ' . $throwable->getMessage();
        $array[] = 'File: ' . $throwable->getFile();
        $array[] = 'Line: ' . $throwable->getLine();
        $array[] = 'Error Code: ' . $throwable->getCode();
        $array[] = 'Trace: ';

        foreach ($throwable->getTrace() as $key => $trace) {
            $traceString = '[' . $key . '] ';
            if (isset($trace['file'], $trace['line'])) {
                $traceString .= $trace['file'] . ' ' . $trace['line'];
            } else {
                $traceString .= 'none';
            }
            $array[] = $traceString;

            if (isset($trace['args'])) {
                foreach ($trace['args'] as $argKey => $arg) {
                    if (is_object($arg)) {
                        $trace['args'][$argKey] = '(object)' . get_class($arg);
                    } elseif (is_array($arg)) {
                        $trace['args'][$argKey] = '(array)' . json_encode($arg);
                    }
                }
            } else {
                $trace['args'] = [];
            }

            $traceString = '';
            if (isset($trace['class'])) {
                $traceString .= $trace['class'] . '->';
            }
            $array[] = $traceString . $trace['function'] . '(' . implode(', ', $trace['args']) . ')' . PHP_EOL;
        }

        return $array;
    }
}

if (!function_exists('shy')) {
    /**
     * Get or make instance
     *
     * @param string $id
     * @param object|string|null $concrete
     * @param array ...$parameters
     *
     * @return object
     */
    function shy($id = null, $concrete = null, ...$parameters)
    {
        if (is_null($id)) {
            return Shy\Core\Container::getContainer();
        }

        return Shy\Core\Container::getContainer()->getOrMake($id, $concrete, ...$parameters);
    }
}

if (!function_exists('bind')) {
    /**
     * Bind ready to make
     *
     * @param string $id
     * @param string|Closure|object|null $concrete
     *
     * @return Shy\Core\Container
     */
    function bind(string $id, $concrete = null)
    {
        return Shy\Core\Container::getContainer()->bind($id, $concrete);
    }
}

if (!function_exists('config')) {
    /**
     * Get config
     *
     * @param string $key
     * @return mixed
     */
    function config(string $key = null)
    {
        if (is_null($key)) {
            return shy(Shy\Core\Contracts\Config::class);
        }

        return shy(Shy\Core\Contracts\Config::class)->find($key);
    }
}

if (!function_exists('require_file')) {
    /**
     * Require file get config
     *
     * @param string $filename
     *
     * @throws Exception
     *
     * @return mixed
     */
    function require_file(string $filename)
    {
        if (file_exists($filename)) {
            return require "$filename";
        } else {
            throw new Exception('require_file() file not exist ' . $filename);
        }
    }
}

if (!function_exists('lang')) {
    /**
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
     * Determine if running in cli.
     *
     * @return bool
     */
    function is_cli()
    {
        return php_sapi_name() === 'cli' || php_sapi_name() === 'phpdbg';
    }
}
