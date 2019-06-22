<?php

use PHPUnit\Framework\TestCase;
use shy\core\container;
use shy\core\config;
use shy\exception\handlerWeb;
use shy\http\exception\postTooLargeException;

class shyExceptionTest extends TestCase
{

    public function setUp()
    {
        global $_SHY_CONTAINER;
        $_SHY_CONTAINER = new container();
        $_SHY_CONTAINER->make('config', new config_key());
        $handler = $_SHY_CONTAINER->make('exception\handler', new handlerWeb());
        $_SHY_CONTAINER->setExceptionHandler($handler);
    }

    /**
     * @expectedException postTooLargeException
     */
    public function testRun()
    {
        throw new postTooLargeException;
    }
}