<?php

return [
    'example' => \app\http\middleware\example::class,
    'group_example' => [
        \shy\Http\middleware\csrf::class,
    ]
];
