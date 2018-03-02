<?php

/**
 * Test
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace controller;

use config\app;
use shy\lib\session;
use app\business\testBusiness;

class test
{
    public function index()
    {
        if (testBusiness::instance()->isMobile()) {
            $info = app::WELCOME . ' in Mobile.';
        } else {
            $info = app::WELCOME;
        }

        if (session::instance()->exist('user')) {
            $info .= ' Again.';
        } else {
            session::instance()->set('user', 1);
        }

        view('test', compact('info'));
    }

}