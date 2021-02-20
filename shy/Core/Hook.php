<?php

namespace Shy\Core;

class Hook
{
    /**
     * @var array
     */
    protected $hooks = [];

    /**
     * Set hook
     *
     * @param string $name
     * @param \Closure $closure
     */
    public function set(string $name, \Closure $closure)
    {
        $this->hooks[$name][] = $closure;
    }

    /**
     * Run
     *
     * @param string $name
     */
    public function run(string $name)
    {
        if (isset($this->hooks[$name])) {
            foreach ($this->hooks[$name] as $hook) {
                call_user_func($hook);
            }
        }
    }
}
