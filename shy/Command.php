<?php

namespace Shy;

use Shy\Core\Contract\Pipeline;
use Exception;

class Command
{
    /**
     * @var array $params
     */
    protected $params = [];

    /**
     * Handle class
     *
     * @var string $class
     */
    protected $class;

    /**
     * Handle method
     *
     * @var $method
     */
    protected $method;

    /**
     * Parse Command
     *
     * @throws Exception
     * @return string Error Message
     */
    protected function parse()
    {
        /**
         * Params
         */
        global $argv;
        array_shift($argv);
        $params = $argv;
        $command = array_shift($params);

        /**
         * Config
         */
        $config = config('command');
        if (!isset($config[$command])) {
            return $this->commandNotFound($command);
        }
        if (!is_array($config[$command])) {
            throw new Exception('Command ' . $command . ' config error.');
        }
        /**
         * Parse router
         */
        $class = key($config[$command]);
        $method = current($config[$command]);
        if (isset($class, $method) && !empty($class) && !empty($method)) {
            if (!class_exists($class)) {
                throw new Exception('Class {' . $class . '} not found.');
            }
            if (!method_exists($class, $method)) {
                throw new Exception('Method {' . $method . '} in class {' . $class . '} not found.');
            }

            $this->class = $class;
            $this->method = $method;
            $this->params = $params;
        } else {
            throw new Exception('Command ' . $command . ' config error.');
        }
    }

    /**
     * Run command
     *
     * @throws Exception
     */
    public function run()
    {
        echo 'Shy Framework ' . shy()->version() . PHP_EOL . PHP_EOL;

        $result = $this->parse();

        if (empty($result)) {
            $result = shy(Pipeline::class)
                ->send(...$this->params)
                ->through($this->class)
                ->via($this->method)
                ->run();
        }

        echo $result . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $command
     * @return string
     */
    protected function commandNotFound($command = '')
    {
        return "Command '{$command}' not found. See 'php command list'.";
    }
}
