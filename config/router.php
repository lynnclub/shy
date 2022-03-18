<?php

return [
    'group' => [
        ['middleware' => ['Example', 'Throttle:10'], 'path' => [
            '/echo/string/with/middleware' => 'test2@echoString',
            '/return/string/with/middleware' => 'test2@returnStringWithRequest',
        ]],
        ['prefix' => 'test/prefix', 'middleware' => ['Throttle:10,5'], 'path' => [
            '/home' => 'home@index',
            '/return/string/with/get/param' => 'home@test',
        ]],
        ['prefix' => 'controller_2', 'namespace' => 'App\\Http\\Controller_2', 'path' => [
            '/home' => 'home@index',
            '/return/string/without/get/param' => 'home@test',
        ]],
        ['middleware' => ['GroupExample'], 'path' => [
            '/test4' => 'test2@echoString',//echo string with middleware
        ]],
        ['middleware' => ['Stop'], 'path' => [
            '/middleware_stop/?' => 'test2@echoString',//echo string with middleware
        ]],
    ],
    'path' => [
        '/' => 'home@index',//view home
        '/test/url/func' => 'home@testUrl',//return string
        'test/path/param/?' => 'home@testPathParam',
        '/smarty' => 'home@smarty',
        '/not/found' => 'home@home3',//404
        '/test/error/500' => 'home@test4',//500
        '/home/path/test' => 'home@index',//view home
        '/testLang' => 'test2@testLang',//zh-CN
        '/testLangUS' => 'test2@testLang2'//en-US
    ]
];
