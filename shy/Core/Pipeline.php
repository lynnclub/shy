<?php

namespace Shy\Core;

use Shy\Core\Contract\Pipeline as PipelineContract;
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

        $return = $pipeline(...$this->passable);

        /**
         * Initialize pipeline after execute
         */
        $this->initialize();

        return $return;
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

        $return = $pipeline(...$this->passable);

        /**
         * Initialize pipeline after execute
         */
        $this->initialize();

        return $return;
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
            return function (...$passable) use ($next, $pipe) {
                $container = Container::getContainer();

                if (empty($next)) {
                    $next = [];
                } else {
                    $next = is_array($next) ? $next : [$next];
                }

                $passable = array_merge($next, $passable);

                if (is_callable($pipe)) {
                    return $container->runFunctionWithDependencyInjection($pipe, ...$passable);
                }

                if (is_string($pipe)) {
                    list($name, $parameters) = $this->parsePipeString($pipe);
                    $pipe = $container->getOrMake($name);
                    if (!is_object($pipe)) {
                        throw new RuntimeException('Class `' . $name . '` cannot make');
                    }

                    $passable = array_merge($passable, $parameters);
                }

                if (method_exists($pipe, $this->method)) {
                    $reflector = new ReflectionMethod($pipe, $this->method);
                    $passable = $container->handleDependencies($reflector->getParameters(), $passable);
                    $method = $this->method;
                } else {
                    throw new RuntimeException('Method ' . $this->method . ' of ' . ($name ?? get_class($pipe)) . ' not exist');
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
        list($name, $parameters) = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }
}
