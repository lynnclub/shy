<?php

return [
    'group' => [
        ['middleware' => ['example'], 'path' => [
            '/test2' => 'test2@test2',
            '/test3' => 'test2@test3'
        ]],
        ['prefix' => 'route', 'path' => [
            '/home' => 'home@index',
            '/test' => 'home@test'
        ]]
    ],
    'path' => [
        '/home2' => 'home@test2',
        '/home3' => 'home@home3'
    ]
];
