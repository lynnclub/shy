<?php

/**
 * Home
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\http\controller;

use shy\core\library\session;
use app\http\business\testBusiness;

use shy\core\library\mysqli;
use shy\core\library\redis;
use shy\core\library\pdo;
use shy\http\facade\request;

class home
{
    public function index()
    {
        if (testBusiness::instance()->isMobile()) {
            $info = 'Hello World in Mobile';
        } else {
            $info = 'Hello World';
        }

        if (session::instance()->exist('user')) {
            $info .= ' Again';
        } else {
            session::instance()->set('user', 1);
        }

        $title = 'Shy Framework';

//        pdo::instance();
//        pdo::close();
//        pdo::closeAll();
//        mysqli::instance();
//        mysqli::close();
//        mysqli::closeAll();
//        redis::instance();
//        redis::close();
//        redis::closeAll();

        return view('home', compact('title', 'info'))->layout('main');
    }

    public function test()
    {
        return 'controller echo test ' . json_encode(request::all());
    }

    public function test2()
    {
        return 'controller echo';
    }
}