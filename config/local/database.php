<?php

return [

    'db' => [
        'default' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'lynncho',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ],
        'db2' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'taste',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ],
        'db3' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'lynncho',
            'username' => 'root',
            'password' => '123456',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ]
    ],
    'redis' => [
        'default' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'database' => 0,
            'password' => ''
        ],
        'db2' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'database' => 1,
            'password' => ''
        ],
        'db3' => [
            'host' => '127.0.0.1',
            'port' => '6379',
            'database' => 0,
            'password' => ''
        ]
    ]

];
