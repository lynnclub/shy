<?php

namespace Shy\Core\Exceptions\Container;

use Exception as PhpException;
use Psr\Container\ContainerExceptionInterface;

class Exception extends PhpException implements ContainerExceptionInterface
{

}
