<?php

namespace App\Http\Controller;

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
        return lang(4110, 'zh-CN');
    }

    public function testLang2()
    {
        return lang(4110, 'en-US');
    }
}
