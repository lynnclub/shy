<?php
/**
 * Pipeline
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\core;

use Closure;
use RuntimeException;

class pipeline
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
     * Init Pipeline
     */
    protected function init()
    {
        $this->passable = [];
        $this->pipes = [];
        $this->method = 'handle';
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
                if (is_callable($pipe)) {
                    return $pipe($next, ...$passable);
                } elseif (is_object($pipe)) {
                    array_unshift($passable, $next);
                    $parameters = $passable;
                } else {
                    list($name, $parameters) = $this->parsePipeString($pipe);
                    $pipe = shy($name);
                    $parameters = array_merge([$next], $passable, $parameters);
                }

                if (!method_exists($pipe, $this->method)) {
                    throw new RuntimeException('Method ' . $this->method . ' not exist');
                } else {
                    $method = $this->method;
                }

                /**
                 * Init param before execute
                 */
                $this->init();
                $response = $pipe->{$method}(...$parameters);

                return $response;
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
