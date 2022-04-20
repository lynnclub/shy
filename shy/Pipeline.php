<?php

namespace Shy;

use Shy\Contract\Pipeline as PipelineContract;
use Closure;
use ReflectionMethod;
use RuntimeException;

class Pipeline implements PipelineContract
{
    /**
     * The object being passed through the pipeline.
     *
     * @var array|mixed
     */
    protected $passable;

    /**
     * The array of class pipes.
     *
     * @var array|mixed
     */
    protected $pipes;

    /**
     * The method to call on each pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * Initialize Pipeline
     */
    protected function initialize()
    {
        $this->passable = [];
        $this->pipes = [];
        $this->method = 'handle';
    }

    /**
     * Set the object being sent through the pipeline.
     *
     * @param  array|mixed ...$passable
     * @return $this
     */
    public function send(...$passable)
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the array of pipes.
     *
     * @param  array|mixed ...$pipes
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
     * Set the method to call on the pipes.
     *
     * @param  string $method
     * @return $this
     */
    public function via($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param  Closure $destination
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
     * Run the pipeline without callback.
     *
     * @return mixed
     */
    public function run()
    {
        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->carry()
        );

        // initialize after reduce
        $this->initialize();

        return $pipeline();
    }

    /**
     * Get the final piece of the Closure onion.
     *
     * @param  Closure $destination
     * @return Closure
     */
    protected function prepareDestination(Closure $destination)
    {
        return function (...$passable) use ($destination) {
            return $destination(...$passable);
        };
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return Closure
     */
    protected function carry()
    {
        return function ($next, $pipe) {
            $passable = $this->passable;
            $method = $this->method;

            return function () use ($next, $pipe, $passable, $method) {
                $container = Container::getContainer();

                if ($next) {
                    array_unshift($passable, $next);
                }

                if (is_string($pipe)) {
                    list($name, $setting) = $this->parsePipeString($pipe);

                    if (!is_object($pipe = $container->getOrMake($name))) {
                        throw new RuntimeException('Class `' . $name . '` is not object');
                    }

                    if ($setting) {
                        $passable = $next ? array_merge([$next], $setting) : $setting;
                    }
                } elseif (is_callable($pipe)) {
                    return $container->executeFunctionWithDependencyInjection($pipe, ...$passable);
                }

                if (method_exists($pipe, $method)) {
                    $reflector = new ReflectionMethod($pipe, $method);
                    $passable = $container->handleDependency($reflector->getParameters(), $passable);
                } else {
                    throw new RuntimeException('Method ' . $method . ' of ' . ($name ?? get_class($pipe)) . ' not exist');
                }

                return $pipe->{$method}(...$passable);
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param string $pipe
     * @return array
     */
    protected function parsePipeString(string $pipe)
    {
        list($name, $setting) = array_pad(explode(':', $pipe, 2), 2, []);
        if (is_string($setting)) {
            $setting = explode(',', $setting);
        }

        return [$name, $setting];
    }
}
