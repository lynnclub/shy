<?php

return [
    'group' => [
        ['middleware' => ['Example', 'Throttle:10'], 'path' => [
            '/echo/string/with/middleware' => 'test2@test2',
            '/return/string/with/middleware' => 'test2@test3',
        ]],
        ['prefix' => 'test/prefix', 'middleware' => ['Throttle:10,5'], 'path' => [
            '/home' => 'home@index',
            '/return/string/with/get/param' => 'home@test',
        ]],
        ['prefix' => 'controller_2', 'namespace' => 'App\\Http\\Controllers_2', 'path' => [
            '/home' => 'home@index',
            '/return/string/without/get/param' => 'home@test',
            '/smarty' => 'home@smarty',
        ]],
        ['middleware' => ['GroupExample'], 'path' => [
            '/test4' => 'test2@test2',//echo string with middleware
        ]],
    ],
    'path' => [
        '/' => 'home@index',//view home
        '/test/url/func' => 'home@test2',//return string
        'test/path/param/?' => 'home@test3',
        '/smarty' => 'home@smarty',
        '/not/found' => 'home@home3',//404
        '/test/error/500' => 'home@test4',//500
        '/home/path/test' => 'home@index',//view home
        '/testLang' => 'test2@testLang',//zh-CN
        '/testLangUS' => 'test2@testLang2'//en-US
    ]
];
