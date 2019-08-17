<?php

return [
    'IpBaffle' => Shy\Http\Middleware\IpBaffle::class,
    'example' => App\Http\Middleware\Example::class,
    'group_example' => [
        Shy\Http\Middleware\Csrf::class,
    ]
];
