<?php

namespace Shy;

use Shy\Contract\Pipeline as PipelineContract;
use Closure;
use ReflectionMethod;
use RuntimeException;

class Pipeline implements PipelineContract
{
    /**
     * 管道内传递的数据
     * Data passed in the pipeline.
     *
     * @var array|mixed
     */
    protected $passable;

    /**
     * 管道对象数组
     * The array of class pipes.
     *
     * @var array|mixed
     */
    protected $pipes;

    /**
     * 管道调用方法
     * The method to call on pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * 设置管道内传递的数据
     * Set the data passed in the pipeline.
     *
     * @param ...$passable
     * @return $this
     */
    public function send(...$passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * 设置管道对象数组
     * Set the array of class pipes.
     *
     * @param ...$pipes
     * @return $this
     */
    public function through(...$pipes)
    {
        if (count($pipes) === 1 && is_array(current($pipes))) {
            $this->pipes = current($pipes);
        } else {
            $this->pipes = $pipes;
        }

        return $this;
    }

    /**
     * 设置管道调用方法
     * Set the method to call on the pipes.
     *
     * @param string $method
     * @return $this
     */
    public function via(string $method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * 带回调执行管道
     * Run the pipeline with a final destination callback.
     *
     * @param Closure $destination
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry(), $this->prepareDestination($destination)
        );

        // initialize after reduce
        $this->initialize();

        return $pipeline();
    }

    /**
     * 不带回调执行管道
     * Run the pipeline without callback.
     *
     * @return mixed
     */
    public function run()
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry()
        );

        // array_reduce后初始化
        // initialize after array_reduce
        $this->initialize();

        return $pipeline();
    }

    /**
     * 获取闭包洋葱的最后一片
     * Get the final piece of the Closure onion.
     *
     * @param Closure $destination
     * @return Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function (...$passable) use ($destination) {
            return $destination(...$passable);
        };
    }

    /**
     * 执行管道
     * execution pipeline.
     *
     * @return Closure
     */
    protected function carry()
    {
        return function ($next, $pipe) {
            $passable = $this->passable;
            $method = $this->method;

            return function () use ($next, $pipe, $passable, $method) {
                if ($next) {
                    array_unshift($passable, $next);
                }

                $container = Container::getContainer();

                if (is_string($pipe)) {
                    list($name, $setting) = $this->parsePipeString($pipe);

                    if (!is_object($pipe = $container->getOrMake($name))) {
                        throw new RuntimeException('Class ' . $name . ' is not object');
                    }

                    if ($setting) {
                        $passable = $next ? array_merge([$next], $setting) : $setting;
                    }
                } elseif (is_callable($pipe)) {
                    return $container->executeFunctionWithDI($pipe, ...$passable);
                }

                if (method_exists($pipe, $method)) {
                    $reflector = new ReflectionMethod($pipe, $method);
                    $passable = $container->handleDI($reflector->getParameters(), $passable);
                } else {
                    throw new RuntimeException(($name ?? get_class($pipe)) . '->' . $method . ' not exist');
                }

                return $pipe->{$method}(...$passable);
            };
        };
    }

    /**
     * 解析管道字符串，获取管道类名与设置
     * Parse full pipe string to get name and setting.
     *
     * @param string $pipe
     * @return array
     */
    protected function parsePipeString(string $pipe)
    {
        list($name, $setting) = array_pad(
            explode(':', $pipe, 2),
            2,
            []
        );

        if (is_string($setting)) {
            $setting = explode(',', $setting);
        }

        return [$name, $setting];
    }

    /**
     * 初始化管道
     * Initialize Pipeline
     */
    protected function initialize()
    {
        $this->passable = [];
        $this->pipes = [];
        $this->method = 'handle';
    }
}
