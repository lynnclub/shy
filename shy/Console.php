<?php

namespace Shy;

use Shy\Core\Contracts\Pipeline;
use Exception;

class Console
{
    /**
     * Command params
     *
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
     * Get Command list
     *
     * @return array
     */
    public function getCommandList()
    {
        return array_keys(config('console'));
    }

    /**
     * Run command
     *
     * @throws Exception
     */
    public function run()
    {
        $this->parseCommand();

        $result = shy(Pipeline::class)
            ->send(...$this->params)
            ->through($this->class)
            ->via($this->method)
            ->run();

        echo PHP_EOL . $result . PHP_EOL . PHP_EOL;
    }

    /**
     * Parse Command
     *
     * @throws Exception
     */
    protected function parseCommand()
    {
        /**
         * Params
         */
        global $argv;
        array_shift($argv);
        $command = array_shift($argv);
        $this->params = $argv;
        /**
         * Config
         */
        $config = config('console');
        if (!isset($config[$command])) {
            die($this->commandNotFoundNotice());
        }
        if (!is_array($config[$command])) {
            throw new Exception('Command ' . $command . ' config error.');
        }
        /**
         * Router
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
        } else {
            throw new Exception('Command ' . $command . ' config error.');
        }
    }

    /**
     * Command not found notice
     *
     * @return string
     */
    protected function commandNotFoundNotice()
    {
        return <<<EOT
        
Command not find.
You can run `php console list` to get all commands.


EOT;
    }

}
