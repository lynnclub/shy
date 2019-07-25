<?php

namespace Shy\Core\Contracts;

use Psr\SimpleCache\CacheInterface;
use ArrayAccess;

interface Cache extends CacheInterface, ArrayAccess
{

}
