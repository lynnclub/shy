<?php

namespace Shy\Core\Contracts;

use Psr\Container\ContainerInterface;
use ArrayAccess;
use Countable;

interface Container extends ContainerInterface, ArrayAccess, Countable
{
    /**
     * Get container
     *
     * @return Container
     */
    public static function getContainer();

    /**
     * Get start id
     *
     * @return string
     */
    public function startId();

    /**
     * Get start time
     *
     * @return string
     */
    public function startTime();

    /**
     * Add forked process id to start id
     *
     * @param int $forkedPid
     */
    public function addForkedPidToStartId(int $forkedPid);

    /**
     * Set instance
     *
     * @param string $id
     * @param $instance
     */
    public function set(string $id, $instance);

    /**
     * Set instances
     *
     * @param array $sets
     */
    public function sets(array $sets);

    /**
     * Bind ready to make
     *
     * @param string $id
     * @param $concrete
     *
     * @return Container
     */
    public function bind(string $id, $concrete = null);

    /**
     * @param array $binds
     */
    public function binds(array $binds);

    /**
     * Is bound
     *
     * @param string $id
     *
     * @return bool
     */
    public function bound(string $id);

    /**
     * Make instance and join to container
     *
     * @param string $id
     * @param object|string|null $concrete
     * @param array ...$parameters
     *
     * @return mixed
     */
    public function make(string $id, $concrete = null, ...$parameters);

    /**
     * @param array $makes
     */
    public function makes(array $makes);

    /**
     * Set alias of instance id
     *
     * @param string $alias
     * @param string $id
     */
    public function alias(string $alias, string $id);

    /**
     * @param array $aliases
     */
    public function aliases(array $aliases);

    /**
     * Make an instance with dependency injection
     *
     * @param string $concrete
     * @param array ...$parameters
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     *
     * @return object
     */
    public function makeClassWithDependencyInjection(string $concrete, ...$parameters);

    /**
     * Run function with dependency injection
     *
     * @param $concrete
     * @param array ...$parameters
     *
     * @throws \ReflectionException
     *
     * @return mixed
     */
    public function runFunctionWithDependencyInjection($concrete, ...$parameters);

    /**
     * Get or make dependency object
     *
     * @param array $parameters
     * @param \ReflectionParameter[] $dependencies
     *
     * @return array
     *
     * @throws \Shy\Core\Exceptions\Container\NotFoundException
     */
    public function getOrMakeDependencies(array $parameters, array $dependencies);

    /**
     * Get or Make instance
     *
     * @param string $id
     * @param object|string $concrete
     * @param array ...$parameters
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     *
     * @return object
     */
    public function getOrMake(string $id, $concrete = null, ...$parameters);

    /**
     * Remove bind and instance
     *
     * @param string|array $id
     */
    public function remove($id);

    /**
     * Get the list of instances id
     *
     * @return array
     */
    public function list();

    /**
     * Get the list of instances memory used
     *
     * @return array
     */
    public function memoryUsed();

}
