<?php

namespace Shy\Contract;

use ArrayAccess;
use Countable;
use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface, ArrayAccess, Countable
{
    /**
     * 获取容器
     * Get container
     *
     * @return Container
     */
    public static function getContainer();

    /**
     * 获取害羞框架版本
     * Get the Shy Framework version
     *
     * @return string
     */
    public function version();

    /**
     * 获取容器启动id
     * Get container start id
     *
     * @return string
     */
    public function startId();

    /**
     * 获取容器启动时间
     * Get container start time
     *
     * @return string
     */
    public function startTime();

    /**
     * 更新派生进程的启动信息
     * Update forked process start information
     *
     * @param int $pid
     */
    public function updateForkedProcessStartInfo(int $pid);

    /**
     * 设置实例到容器池
     * Set instance to container pool
     *
     * @param string $id
     * @param $instance
     * @return Container
     */
    public function set(string $id, $instance);

    /**
     * 批量设置实例到容器池
     * Batch set instances to container pool
     *
     * @param array $sets
     */
    public function sets(array $sets);

    /**
     * 处理依赖注入
     * Handle dependency injection
     *
     * @param \ReflectionParameter[] $dependencies
     * @param array $parameters
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function handleDependency(array $dependencies, array $parameters = []);

    /**
     * 通过反射制作实例，支持依赖注入
     * Make an instance by reflection, support dependency injection
     *
     * @param string $concrete
     * @param mixed ...$parameters
     * @return object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function makeInstanceWithDependencyInjection(string $concrete, ...$parameters);

    /**
     * 执行匿名函数，支持依赖注入
     * Execute anonymous functions, support dependency injection
     *
     * @param $concrete
     * @param mixed ...$parameters
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \ReflectionException
     */
    public function executeFunctionWithDependencyInjection($concrete, ...$parameters);

    /**
     * 绑定用于制作的材料
     * Bind materials for make
     *
     * @param string $id
     * @param $concrete
     * @return $this
     */
    public function bind(string $id, $concrete = null);

    /**
     * 批量绑定用于制作的材料
     * Batch bind materials for make
     *
     * @param array $binds
     * @return $this
     */
    public function binds(array $binds);

    /**
     * 是否已绑定
     * Is bound
     *
     * @param string $id
     * @return bool
     */
    public function isBound(string $id);

    /**
     * 制作实例，并加入容器
     * Make an instance and join to the container
     *
     * @param string $id
     * @param null $concrete
     * @param mixed ...$parameters
     * @return mixed
     */
    public function make(string $id, $concrete = null, ...$parameters);

    /**
     * 获取或制作实例
     * Get or make instance
     *
     * @param string $id
     * @param null $concrete
     * @param mixed ...$parameters
     * @return mixed|object
     */
    public function getOrMake(string $id, $concrete = null, ...$parameters);

    /**
     * 设置实例id的别名
     * Set alias for instance id
     *
     * @param string $alias
     * @param string $id
     * @return Container
     */
    public function alias(string $alias, string $id);

    /**
     * 批量设置实例id的别名
     * Batch set alias for instance id
     *
     * @param array $aliases
     * @return $this
     */
    public function aliases(array $aliases);

    /**
     * 从容器中移除绑定材料与实例
     * Remove bound materials and instances from the container
     *
     * @param string|array $id
     */
    public function remove($id);

    /**
     * 获取容器中的实例id列表
     * Get a list of instance ids in the container
     *
     * @return array
     */
    public function list();
}
