<?php

/**
 * middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\core;

interface middleware
{
    public function handle(...$passable);
}
