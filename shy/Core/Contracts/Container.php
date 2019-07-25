<?php

namespace Shy\Core\Contracts;

use Psr\Container\ContainerInterface;
use ArrayAccess;

interface Container extends ContainerInterface, ArrayAccess
{
    /**
     * Get start id
     *
     * @return string
     */
    public function startId();

    /**
     * Add forked process id to start id
     *
     * @param int $forkedPid
     */
    public function addForkedPidToStartId(int $forkedPid);

    /**
     * Bind ready to make
     *
     * @param string $id
     * @param string|\Closure|object|null $concrete
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     *
     * @return Container
     */
    public function bind(string $id, $concrete);

    /**
     * Is bound
     *
     * @param string $id
     *
     * @return bool
     */
    public function bound(string $id);

    /**
     * Get bound
     *
     * @param string $id
     *
     * @return string|\Closure|object|false
     */
    public function getBound(string $id);

    /**
     * Make instance and join the container
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
    public function make(string $id, $concrete = null, ...$parameters);

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
