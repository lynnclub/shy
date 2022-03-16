<?php

namespace Shy\Contract;

use Closure;

interface Middleware
{
    public function handle(Closure $next, ...$passable);
}
