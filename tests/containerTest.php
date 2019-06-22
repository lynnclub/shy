<?php

use PHPUnit\Framework\TestCase;
use shy\core\container;

class containerTest extends TestCase
{
    protected $container;

    public function setUp()
    {
        global $_SHY_CONTAINER;
        $this->container = $_SHY_CONTAINER = new container;
    }

    public function testMakeClosureWithParam()
    {
        $object = $this->container->make('StdClass', function (...$param) {
            return new StdClass(...$param);
        }, 'test', 'test2');
        $this->assertInstanceOf(StdClass::class, $object);
        $this->assertInstanceOf(StdClass::class, shy('StdClass'));
    }

    /**
     * @depends testMakeClosureWithParam
     */
    public function testBindMakeClosureWithParam()
    {
        $object = $this->container->bind('StdClass2', function (...$param) {
            return new StdClass(...$param);
        })->make('StdClass2', 'test', 'test2');
        $this->assertInstanceOf(StdClass::class, $object);
        $this->assertEquals(shy('StdClass'), shy('StdClass2'));
    }

    /**
     * @depends testBindMakeClosureWithParam
     */
    public function testMakeClosure()
    {
        $object = $this->container->make('StdClass3', function () {
            return new StdClass();
        });
        $this->assertInstanceOf(StdClass::class, $object);
        $this->assertEquals(shy('StdClass3'), shy('StdClass2'));
    }

    /**
     * @depends testMakeClosure
     * @expectedException RuntimeException
     */
    public function testClear()
    {
        $this->container->clear('StdClass3');
        shy('StdClass3');
    }

    public function testMakeObjectWithParam()
    {
        $this->container->make('StdClass3', new StdClass('test', 'test2'));
        $this->assertEquals(shy('StdClass'), shy('StdClass3'));
        $this->container->clear('StdClass3');
    }

    public function testBindMakeObjectWithParam()
    {
        $this->container->bind('StdClass3', new StdClass('test', 'test2'))->make('StdClass3');
        $this->assertEquals(shy('StdClass'), shy('StdClass3'));
        $this->container->clear('StdClass3');
    }

    public function testMakeObject()
    {
        $object = $this->container->make('StdClass3', new StdClass);
        $this->assertEquals(shy('StdClass2'), shy('StdClass3'));
        $this->assertInstanceOf(StdClass::class, $object);
    }

    public function testMakeClass()
    {
        $object = $this->container->make('shy\core\container');
        $this->assertEquals($this->container, $object);
        $this->assertEquals($this->container, shy('shy\core\container'));
    }

    public function testMakeClassWithParam()
    {
        $object = $this->container->make('myConstruct', '1', '5', '3');
        $new = new myConstruct('1', '5');
        $this->assertEquals($new->getTest(), $object->getTest());
        $this->assertEquals($new->getTest2(), $object->getTest2());
    }

    /**
     * @depends testMakeClass testMakeClassWithParam
     */
    public function testGetList()
    {
        $list = $this->container->getList();
        $this->assertArrayHasKey('myConstruct', $list);
        $this->assertArrayHasKey('shy\core\container', $list);
    }

    /**
     * @depends testMakeObject
     * @expectedException RuntimeException
     */
    public function testClearAll()
    {
        $this->container->clearAll();
        shy('StdClass2');
        shy('shy\core\container');
    }

}

class myConstruct
{
    private $test;

    private $test2;

    public function __construct($test, $test2)
    {
        $this->test = $test;
        $this->test2 = $test2;
    }

    public function getTest()
    {
        return $this->test;
    }

    public function getTest2()
    {
        return $this->test2;
    }
}
