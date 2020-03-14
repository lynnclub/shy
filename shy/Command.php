<?php

namespace Shy;

use Shy\Core\Contracts\Pipeline;
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
     * @return array
     */
    public function getList()
    {
        return array_keys(config('command'));
    }

    /**
     * Parse Command
     *
     * @throws Exception
     */
    protected function parse()
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
        $config = config('command');
        if (!isset($config[$command])) {
            $this->notFound($command);
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
        $this->parse();

        $result = shy(Pipeline::class)
            ->send(...$this->params)
            ->through($this->class)
            ->via($this->method)
            ->run();

        echo PHP_EOL . $result . PHP_EOL . PHP_EOL;
    }

    /**
     * @param string $command
     * @return string
     */
    protected function notFound($command = '')
    {
        echo <<<EOT
        
Command '{$command}' not find.
You can run `php command list` to get all commands.


EOT;
        exit(1);
    }
}
