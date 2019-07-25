<?php

namespace Shy\Core\Contracts;

use Closure;

interface Middleware
{
    public function handle(Closure $next, ...$passable);
}
