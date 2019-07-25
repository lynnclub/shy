<?php

namespace shy;

use Shy\Core\Container;
use Shy\Core\Contracts\Config as ConfigContract;
use Shy\Core\Config;
use Shy\Core\Contracts\Pipeline;
use Shy\Core\Contracts\Pipeline as PipelineContract;
use Exception;

class Console extends Container
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

    public function __construct(string $configDir = '')
    {
        static::setInstance($this);

        $this->make(ConfigContract::class, new Config($configDir));

        $this->registerAliases();

        $this->systemSetting();

        if (config_key('illuminate_database')) {
            init_illuminate_database();
        }
    }

    protected function registerAliases()
    {
        $this->alias(ConfigContract::class, 'config');
        $this->alias(PipelineContract::class, 'pipeline');
    }

    protected function systemSetting()
    {
        date_default_timezone_set(config_key('timezone'));
    }

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
    public function runCommand()
    {
        $this->bootstrap();

        $result = shy(pipeline::class)
            ->send(...$this->params)
            ->through($this->class)
            ->via($this->method)
            ->run();

        echo PHP_EOL . $result . PHP_EOL . PHP_EOL;
    }

    /**
     * Bootstrap
     *
     * @throws Exception
     */
    protected function bootstrap()
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
        if (!is_string($config[$command])) {
            throw new Exception('Command ' . $command . ' config error.');
        }
        /**
         * Router
         */
        $route = explode('@', $config[$command]);
        if (isset($route[0], $route[1]) && !empty($route[0]) && !empty($route[1])) {
            if (!class_exists($class = 'shy\\console\\command\\' . $route[0])
                && !class_exists($class = 'app\\console\\' . $route[0])
                && !class_exists($class = $route[0])
            ) {
                throw new Exception('Class {' . $route[0] . '} not found.');
            }
            if (!method_exists($class, $route[1])) {
                throw new Exception('Method {' . $route[1] . '} in class {' . $class . '} not found.');
            }

            $this->class = $class;
            $this->method = $route[1];
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
