<?php

use PHPUnit\Framework\TestCase;
use Shy\Core\Container;

class ContainerTest extends TestCase
{
    protected $container;

    protected function setUp()
    {
        $this->container = Container::getContainer();
    }

    /*
     * -------------------------------------
     * Closure
     * -------------------------------------
     */

    public function testBindClosure()
    {
        /**
         * Bind without params
         */
        $this->container->bind('Closure\stdClass', function () {
            return new stdClass();
        });
        $this->assertTrue($this->container->bound('Closure\stdClass'));

        /**
         * Bind with params
         */
        $this->container->bind('Closure\myClass2', function ($test1, $this2) {
            return new myClass($test1, $this2);
        });
        $this->assertTrue($this->container->bound('Closure\myClass2'));

        /**
         * Bind with Closure params
         */
        $this->container->bind('Closure\myClass3', function (Closure $test1, $this2) {
            return new myClass($test1, $this2);
        });
    }

    /**
     * @depends testBindClosure
     */
    public function testMakeClosure()
    {
        /**
         * Make without params
         */
        $this->assertTrue($this->container->bound('Closure\stdClass'));
        $object = $this->container->make('Closure\stdClass');
        $this->assertInstanceOf(stdClass::class, $object);

        /**
         * Make with params
         */
        $object = $this->container->make('Closure\myClass2', 'param1', 'param2');
        $this->assertInstanceOf(myClass::class, $object);
        $this->assertEquals('param2', $object->getTest2());

        /**
         * Bind with Closure params
         */
        $object = $this->container->make('Closure\myClass3', function ($test) {
            return 'Closure_' . $test;
        }, 'param2');
        $this->assertInstanceOf(myClass::class, $object);
        $this->assertInstanceOf(Closure::class, $object->getTest());

        /**
         * Closure params without bind
         */
        $object = $this->container->make('Closure\myClass3', function ($test1, $this2) {
            return new myClass($test1, $this2);
        }, 'param1', 'param2');
        $this->assertInstanceOf(myClass::class, $object);
        $this->assertEquals('param1', $object->getTest());

        /**
         * Bind with Closure params
         */
        $object = $this->container->make('Closure\myDependencyClass', function ($test1, stdClass $this2) {
            return new myDependencyClass($test1, $this2);
        }, 'param1');
        $this->assertInstanceOf(myDependencyClass::class, $object);
        $this->assertInstanceOf(stdClass::class, $object->getTest2());
    }

    /*
     * -------------------------------------
     * Object
     * -------------------------------------
     */

    public function testBindObject()
    {
        /**
         * Bind without params
         */
        $this->container->bind('object\stdClass2', new stdClass());
        $this->assertTrue($this->container->bound('object\stdClass2'));

        /**
         * Bind with params
         */
        $this->container->bind('object\myClass3', new myClass(1, 123));
        $this->assertTrue($this->container->bound('object\myClass3'));
    }

    /**
     * @depends testBindObject
     */
    public function testMakeObject()
    {
        /**
         * Make without params
         */
        $this->assertTrue($this->container->bound('object\stdClass2'));
        $object = $this->container->make('object\stdClass2');
        $this->assertInstanceOf(stdClass::class, $object);

        /**
         * Make with params
         */
        $object = $this->container->make('object\myClass3');
        $this->assertInstanceOf(myClass::class, $object);
        $this->assertEquals(123, $object->getTest2());
    }

    /*
     * -------------------------------------
     * Namespace class
     * -------------------------------------
     */

    public function testBindClass()
    {
        $this->container->bind(myClass::class);
        $this->assertTrue($this->container->bound(myClass::class));

        $this->container->bind(myDependency::class, myDependencyClass::class);
        $this->assertTrue($this->container->bound(myDependency::class));
    }

    /**
     * @depends testBindClass
     */
    public function testMakeClass()
    {
        /**
         * Make without params
         */
        $this->assertTrue($this->container->bound(myClass::class));
        $object = $this->container->make(myClass::class);
        $this->assertInstanceOf(myClass::class, $object);

        /**
         * Make with params
         */
        $object = $this->container->make(myDependency::class, 'param1');
        $this->assertInstanceOf(stdClass::class, $object->getTest2());
    }

    /*
     * -------------------------------------
     * Others
     * -------------------------------------
     */

    /**
     * @depends testMakeClass
     */
    public function testOther()
    {
        $object = $this->container->get(myDependency::class);
        $this->assertInstanceOf(myDependencyClass::class, $object);
        $this->container->remove(myDependencyClass::class);
        $this->assertFalse($this->container->has(myDependencyClass::class));
    }

}

class myClass
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

interface myDependency
{

}

class myDependencyClass implements myDependency
{
    private $test;

    private $test2;

    public function __construct($test, stdClass $test2)
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
