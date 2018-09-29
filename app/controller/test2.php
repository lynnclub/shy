<?php

/**
 * Home
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\controller;

use shy\http\facade\request;

class test2
{
    public function test3()
    {
        return 'controller echo test ' . json_encode(request::all());
    }

    public function test2()
    {
        echo 'controller echo';
    }
}