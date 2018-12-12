<?php

return [

    /*
    | Base Url
    |
    | main domain. (optional)
    */

    'base_url' => '',

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

    'smarty' => [
        'cache_lifetime' => '',
        'left_delimiter' => '',
        'right_delimiter' => ''
    ]

];
