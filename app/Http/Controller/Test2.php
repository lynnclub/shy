<?php

namespace App\Http\Controller;

use Shy\Http\Facade\Request;

class Test2
{
    public function returnStringWithRequest()
    {
        return 'controller echo test ' . json_encode(Request::all());
    }

    public function echoString()
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
