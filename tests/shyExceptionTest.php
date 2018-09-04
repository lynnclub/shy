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
        global $_container;
        $_container = new container();
        $_container->make('config', new config());
        $handler = $_container->make('exception\handler', new handlerWeb());
        $_container->setExceptionHandler($handler, config('env'));
    }

    /**
     * @expectedException postTooLargeException
     */
    public function testRun()
    {
        throw new postTooLargeException;
    }
}