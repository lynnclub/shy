<?php

/**
 * Home
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\controller;

use shy\library\session;
use app\business\testBusiness;

//use shy\lib\db;
//use shy\lib\redis;
//use shy\libraries\pdo;
use shy\facade\request;

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

//        pdo::instance();
//        pdo::close();
//        pdo::closeAll();
//        db::instance();
//        db::close();
//        db::closeAll();
//        redis::instance();
//        redis::close();
//        redis::closeAll();

        return view('home', compact('info'))->layout('main');
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