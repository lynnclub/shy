<?php

namespace App\Http\Controllers;

use Shy\Http\Facades\Request;

class Test2
{
    public function test3()
    {
        return 'controller echo test ' . json_encode(Request::all());
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
