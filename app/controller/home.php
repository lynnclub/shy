<?php

/**
 * Home
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\controller;

use shy\lib\session;
use app\business\testBusiness;

class home
{
    public function index()
    {
        if (testBusiness::instance()->isMobile()) {
            $info = 'WELCOME in Mobile.';
        } else {
            $info = 'WELCOME';
        }

        if (session::instance()->exist('user')) {
            $info .= ' AGAIN';
        } else {
            session::instance()->set('user', 1);
        }

        view('home', compact('info'));
    }
}