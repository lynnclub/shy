<?php

return [
    'Csrf' => Shy\Http\Middleware\Csrf::class,
    'IpWhitelist' => Shy\Http\Middleware\IpWhitelist::class,
    'Throttle' => Shy\Http\Middleware\Throttle::class,
    'Example' => App\Http\Middleware\Example::class,
    'GroupExample' => [
        'Csrf' => Shy\Http\Middleware\Csrf::class,
    ]
];
