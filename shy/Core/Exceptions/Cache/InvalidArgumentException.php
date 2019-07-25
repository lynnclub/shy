<?php

namespace Shy\Core\Exceptions\Cache;

use Exception;
use Psr\SimpleCache\InvalidArgumentException as InvalidArgumentExceptionInterface;

class InvalidArgumentException extends Exception implements InvalidArgumentExceptionInterface
{

}
