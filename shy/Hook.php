<?php

namespace Shy;

use Closure;

class Hook
{
    /**
     * 钩子池
     *
     * @var array
     */
    protected $hooks = [];

    /**
     * 设置钩子
     * Set hook
     *
     * @param string $name
     * @param Closure $closure
     */
    public function set(string $name, Closure $closure)
    {
        $this->hooks[$name][] = $closure;
    }

    /**
     * 执行
     * Run
     *
     * @param string $name
     * @param ...$param
     */
    public function run(string $name, ...$param)
    {
        if (isset($this->hooks[$name])) {
            foreach ($this->hooks[$name] as $hook) {
                call_user_func($hook, ...$param);
            }
        }
    }
}
