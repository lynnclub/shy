<?php
/**
 * Home
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace app\http\controller_2;

use shy\http\facade\session;
use shy\http\facade\request;
use app\http\facade\testBusiness;

use shy\core\facade\redis;
use shy\http\facade\capsule;
use Illuminate\Database\Capsule\Manager;

class home
{
    public function index()
    {
        if (testBusiness::isMobile()) {
            $info = 'Hello World in Mobile';
        } else {
            $info = 'Hello World';
        }

        if (session::exist('user')) {
            $info .= ' Again';
        } else {
            session::set('user', 1);
        }

        $title = 'Shy Framework';

//        dd(shy(Manager::class)::table('users')->where('id', 1)->get());
//        dd(capsule::table('users')->where('id', 2)->get());
//        redis::instance();
//        redis::close();
//        redis::closeAll();

        return view('home', compact('title', 'info'))->layout('main');
    }

    public function smarty()
    {
        if (testBusiness::isMobile()) {
            $params['info'] = 'Hello World in Mobile';
        } else {
            $params['info'] = 'Hello World';
        }

        if (session::exist('user')) {
            $params['info'] .= ' Again';
        } else {
            session::set('user', 1);
        }

        $params['title'] = 'Shy Framework';

        return smarty('smarty.tpl', $params);
    }

    public function test()
    {
        return 'controller echo test ' . json_encode(request::all());
    }
}
