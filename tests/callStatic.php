<?php

class callStaticA
{
    public static function __callStatic($method, $parameters)
    {
        //return (new callStaticB)->$method(...$parameters); error

        if (method_exists(callStaticB::class, $method)) {
            return (new callStaticB)->$method(...$parameters);
        } else {
            return callStaticB::$method(...$parameters);
        }
    }
}

class callStaticB
{
    public function testB()
    {
        echo 'testB';
    }

    public static function __callStatic($method, $parameters)
    {
        return (new callStaticC())->$method(...$parameters);
    }
}

class callStaticC
{
    public function test()
    {
        echo 'testC';
    }
}

callStaticA::test();
