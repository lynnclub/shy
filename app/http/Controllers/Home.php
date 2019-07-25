<?php

namespace App\Http\Controllers;

use App\Http\Business\TestBusiness;
use App\Http\Facades\TestBusiness as StaticTestBusiness;
use Shy\Http\Session;
use Shy\Http\Facades\Request;

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

        return smarty('smarty.tpl', $params);
    }

    public function test()
    {
        return 'controller echo test ' . json_encode(Request::all());
    }

    public function test2()
    {
        return 'controller echo';
    }
}
