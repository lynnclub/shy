<?php

namespace Shy\Exception\Cache;

use Exception;
use Psr\SimpleCache\InvalidArgumentException as InvalidArgumentExceptionInterface;

class InvalidArgumentException extends Exception implements InvalidArgumentExceptionInterface
{

}
