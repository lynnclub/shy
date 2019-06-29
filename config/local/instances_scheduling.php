<?php
/**
 * Instances scheduling
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

return [

    /*
    | Switch
    */

    'switch' => true,

    /*
    | Avoid list of scheduling
    */

    'avoid_list' => [
        shy\http::class,
        shy\core\pipeline::class,
        shy\http\request::class,
        shy\http\router::class,
        shy\http\view::class,
        shy\http\session::class,
        shy\http\response::class
    ]

];
