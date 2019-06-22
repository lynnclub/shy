<?php

/**
 * Test 2
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\http\controller;

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

    public function testLang()
    {
        set_lang('zh-CN');
        return lang(110);
    }

    public function testLang2()
    {
        set_lang('en-US');
        return lang(110);
    }
}
