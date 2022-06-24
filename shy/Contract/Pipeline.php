<?php

namespace Shy\Contract;

use Closure;

interface Pipeline
{
    /**
     * 设置管道内传递的数据
     * Set the data passed in the pipeline.
     *
     * @param ...$passable
     * @return $this
     */
    public function send(...$passable);

    /**
     * 设置管道对象数组
     * Set the array of class pipes.
     *
     * @param ...$pipes
     * @return $this
     */
    public function through(...$pipes);

    /**
     * 设置管道调用方法
     * Set the method to call on the pipes.
     *
     * @param string $method
     * @return $this
     */
    public function via(string $method);

    /**
     * 带回调执行管道
     * Run the pipeline with a final destination callback.
     *
     * @param Closure $destination
     * @return mixed
     */
    public function then(Closure $destination);

    /**
     * 不带回调执行管道
     * Run the pipeline without callback.
     *
     * @return mixed
     */
    public function run();
}
