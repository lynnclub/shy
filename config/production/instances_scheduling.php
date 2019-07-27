<?php

return [

    /*
    | Switch
    */

    'switch' => true,

    /*
    | Avoid list of scheduling
    */

    'avoid_list' => [
        Shy\Core\Contracts\Config::class,
        Shy\Core\Contracts\Logger::class,
        Shy\Core\Contracts\ExceptionHandler::class,
        Shy\Core\Contracts\Pipeline::class,
        Shy\Core\Contracts\Cache::class,
        Shy\Http::class,
        Shy\Http\Contracts\Request::class,
        Shy\Http\Contracts\Router::class,
        Shy\Http\Contracts\View::class,
        Shy\Http\Contracts\Session::class,
        Shy\Http\Contracts\Response::class,
        Shy\Console::class,
    ]

];
