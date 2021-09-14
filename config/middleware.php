<?php

return [
    'Csrf' => Shy\Http\Middleware\Csrf::class,
    'IpWhitelist' => Shy\Http\Middleware\IpWhitelist::class,
    'Throttle' => Shy\Http\Middleware\Throttle::class,
    'Example' => App\Http\Middleware\Example::class,
    'Stop' => App\Http\Middleware\Stop::class,
    'GroupExample' => [
        Shy\Http\Middleware\Csrf::class,
        Shy\Http\Middleware\IpWhitelist::class,
    ]
];
