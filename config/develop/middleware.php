<?php

return [
    'example' => \app\http\middleware\example::class,
    'group_example' => [
        \shy\http\middleware\csrf::class,
    ]
];
