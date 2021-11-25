<?php

namespace Shy\Core\Contract;

use Closure;

interface Middleware
{
    public function handle(Closure $next, ...$passable);
}
