<?php

return [
    'group' => [
        ['middleware' => ['example'], 'path' => [
            '/test2' => 'test2@test2',//echo string with middleware
            '/test3' => 'test2@test3'//return string with middleware
        ]],
        ['prefix' => 'route', 'path' => [
            '/home' => 'home@index',//view home
            '/test' => 'home@test'//return string
        ]]
    ],
    'path' => [
        '/home2' => 'home@test2',//return string
        '/smarty' => 'home@smarty',
        '/home3' => 'home@home3',//404
        '/home/path/test' => 'home@index',//view home
        '/testLang' => 'test2@testLang',//zh-CN
        '/testLang2' => 'test2@testLang2'//en-US
    ]
];
