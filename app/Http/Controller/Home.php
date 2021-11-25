<?php

namespace App\Http\Controller;

use App\Http\Business\TestBusiness;
use App\Http\Facades\TestBusiness as StaticTestBusiness;
use Shy\Http\Session;
use Shy\Http\Contract\Request;
use Shy\Core\Facade\Cache;

class Home
{
    public function index(TestBusiness $business, Session $session)
    {
        if ($business->isMobile()) {
            $info = 'Hello World in Mobile';
        } else {
            $info = 'Hello World';
        }

        if ($session->exist('user')) {
            $info .= ' Again';
        } else {
            $session->set('user', 1);
        }

        $title = 'Shy Framework';

        Cache::set('info', $info);
        $info = Cache::get('info');

        return view('home', compact('title', 'info'))->layout('main');
    }

    public function smarty(Session $session)
    {
        if (StaticTestBusiness::isMobile()) {
            $params['info'] = 'Hello World in Mobile';
        } else {
            $params['info'] = 'Hello World';
        }

        if ($session->exist('user')) {
            $params['info'] .= ' Again';
        } else {
            $session->set('user', 1);
        }

        $params['title'] = 'Shy Framework';

        $params['shy'] = shy();

        return smarty('smarty.tpl', $params);
    }

    public function test(Request $request)
    {
        return 'controller echo test ' . json_encode($request->all());
    }

    public function test2()
    {
        return 'controller echo ' . url('controller_2/home');
    }

    public function test3($pathParam)
    {
        return 'controller echo path param ' . $pathParam;
    }

    public function test4()
    {
        return test_error();
    }
}
