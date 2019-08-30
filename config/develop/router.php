<?php

return [
    'group' => [
        ['middleware' => ['Example', 'Throttle:10'], 'path' => [
            '/test2' => 'test2@test2',//echo string with middleware
            '/test3' => 'test2@test3'//return string with middleware
        ]],
        ['prefix' => 'route', 'middleware' => ['Throttle:10,5'], 'path' => [
            '/home' => 'home@index',//view home
            '/test' => 'home@test'//return string
        ]],
        ['prefix' => 'controller_2', 'namespace' => 'App\\Http\\Controllers_2', 'path' => [
            '/home' => 'home@index',//view home
            '/test' => 'home@test',//return string
            '/smarty' => 'home@smarty'
        ]],
        ['middleware' => ['GroupExample'], 'path' => [
            '/test4' => 'test2@test2',//echo string with middleware
        ]],
    ],
    'path' => [
        '/' => 'home@index',//view home
        '/home2' => 'home@test2',//return string
        '/smarty' => 'home@smarty',
        '/home3' => 'home@home3',//404
        '/home/path/test' => 'home@index',//view home
        '/testLang' => 'test2@testLang',//zh-CN
        '/testLang2' => 'test2@testLang2'//en-US
    ]
];
