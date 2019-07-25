<?php

namespace Shy\Core;

use Shy\Core\Contracts\Pipeline as PipelineContract;
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

        return $pipeline(...$this->passable);
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

        return $pipeline(...$this->passable);
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
            $method = $this->method;
            /**
             * Initialize pipeline before execute
             */
            $this->initialize();

            return function (...$passable) use ($next, $pipe, $method) {
                if (is_callable($pipe)) {
                    return shy()->runViaReflectionFunction($pipe, $next, ...$passable);
                } else {
                    if (is_object($pipe)) {
                        array_unshift($passable, $next);
                        $parameters = $passable;
                    } else {
                        list($name, $parameters) = $this->parsePipeString($pipe);
                        $pipe = shy($name);
                        $parameters = array_merge([$next], $passable, $parameters);
                    }

                    if (method_exists($pipe, $method)) {
                        $reflector = new ReflectionMethod($pipe, $method);
                        $parameters = shy()->getDependencies($parameters, $reflector->getParameters());
                    } else {
                        throw new RuntimeException('Method ' . $method . ' not exist');
                    }

                    $response = $pipe->{$method}(...$parameters);

                    return $response;
                }
            };
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param  string $pipe
     * @return array
     */
    protected function parsePipeString($pipe)
    {
        list($name, $parameters) = array_pad(explode(':', $pipe, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }
}
