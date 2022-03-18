<?php

namespace App\Http\Controller;

use App\Http\Business\TestBusiness;
use App\Http\Facade\TestBusiness as StaticTestBusiness;
use Shy\Http\Contract\Session;
use Shy\Http\Contract\Request;
use Shy\Facade\Cache;

class Home
{
    public function index(TestBusiness $business, Session $session)
    {
        if ($business->isMobile()) {
            $info = '你好，移动端 Hello Mobile';
            $infoEng = 'Hello Mobile';
        } else {
            $info = '你好，世界';
            $infoEng = 'Hello World';
        }

        if ($session->exist('user')) {
            if ($business->isMobile()) {
                $info = '你好，移动端，又见面了！';
                $infoEng = 'Hello Mobile, Again!';
            } else {
                $info = '你好，世界，又见面了！';
                $infoEng = 'Hello World, Again!';
            }
        } else {
            $session->set('user', true);
        }

        $title = '害羞框架 Shy Framework';

        Cache::set('info', $info);
        $info = Cache::get('info');

        return view('home', compact('title', 'info', 'infoEng'))
            ->layout('main');
    }

    public function smarty(Session $session)
    {
        if (StaticTestBusiness::isMobile()) {
            $params['info'] = '你好，移动端 Hello Mobile';
            $params['infoEng'] = 'Hello Mobile';
        } else {
            $params['info'] = '你好，世界';
            $params['infoEng'] = 'Hello World';
        }

        if ($session->exist('user')) {
            if (StaticTestBusiness::isMobile()) {
                $params['info'] = '再次！你好，移动端';
                $params['infoEng'] = 'Hello Mobile, Again!';
            } else {
                $params['info'] = '再次！你好，世界';
                $params['infoEng'] = 'Hello World, Again!';
            }
        } else {
            $session->set('user', true);
        }

        $params['title'] = '害羞框架 Shy Framework';

        Cache::set('info', $params['info']);
        $params['info'] = Cache::get('info');

        $params['shy'] = shy();

        return smarty('smarty.tpl', $params);
    }

    public function test(Request $request)
    {
        return 'controller echo test ' . json_encode($request->all());
    }

    public function testUrl()
    {
        return 'controller echo ' . url('controller_2/home');
    }

    public function testPathParam($pathParam)
    {
        return 'controller echo path param ' . $pathParam;
    }

    public function test4()
    {
        return test_error();
    }
}
