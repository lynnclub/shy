<?php

/**
 * middleware
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\core;

use Closure;

interface middleware
{
    public function handle($request, Closure $next);
}