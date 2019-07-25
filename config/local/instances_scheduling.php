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
        shy\Core\pipeline::class,
        shy\Http\request::class,
        shy\Http\router::class,
        shy\Http\view::class,
        shy\Http\session::class,
        shy\Http\response::class
    ]

];
