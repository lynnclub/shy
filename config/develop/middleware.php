<?php

return [
    'example' => \App\Http\Middleware\Example::class,
    'group_example' => [
        \Shy\Http\Middleware\Csrf::class,
    ]
];
