<?php

namespace Shy;

use Shy\Contract\Pipeline;
use Exception;

class Command
{
    /**
     * 参数
     *
     * @var array $params
     */
    protected $params = [];

    /**
     * 处理类
     * Handle class
     *
     * @var string $class
     */
    protected $class;

    /**
     * 处理方法
     * Handle method
     *
     * @var $method
     */
    protected $method;

    /**
     * 解析命令
     * Parse Command
     *
     * @return string Error Message
     *
     * @throws Exception
     */
    protected function parse()
    {
        /**
         * 参数
         * Params
         */
        global $argv;
        array_shift($argv);
        $params = $argv;
        $command = array_shift($params);

        /**
         * 配置
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
         * 解析路由
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
     * 运行命令
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
     * 命令未找到
     *
     * @param string $command
     * @return string
     */
    protected function commandNotFound(string $command = '')
    {
        return "Command '{$command}' not found. See 'php command list'.";
    }
}
