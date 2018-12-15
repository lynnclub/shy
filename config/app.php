<?php

return [

    /*
    | Base Url
    |
    | main domain. (optional)
    */

    'base_url' => '',

    /*
    | Worker Man
    */

    'worker_man' => [
        'port' => 2348,
        'worker' => 4
    ],

    /*
    | Environment
    |
    | for error reporting.
    | options: development, production.
    */

    'env' => 'development',

    /*
    | Time Zone
    |
    | for time() date()...
    | options: PRC, Asia/Shanghai, Asia/Tokyo...
    */

    'timezone' => 'PRC',

    /*
    | Slow Log
    */

    'slow_log' => true,

    /*
    | Slow Log Limit (second)
    */

    'slow_log_limit' => 1,

    /*
    | Route By Config
    */

    'route_by_config' => true,

    /*
    | Route By Path
    */

    'route_by_path' => true,

    /*
    | Default Controller
    */

    'default_controller' => 'home',

    /*
    | Smarty
    */

    'smarty' => true,

    /*
    | Smarty Config
    */

    'smarty_config' => [
        'left_delimiter' => '',
        'right_delimiter' => '',
        'caching' => false,
        'cache_lifetime' => 120 //seconds
    ]

];
