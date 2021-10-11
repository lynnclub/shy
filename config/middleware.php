<?php

return [
    'GetOnly' => Shy\Http\Middleware\GetOnly::class,
    'PostOnly' => Shy\Http\Middleware\PostOnly::class,
    'CSRF' => Shy\Http\Middleware\CSRF::class,
    'IpWhitelist' => Shy\Http\Middleware\IpWhitelist::class,
    'Throttle' => Shy\Http\Middleware\Throttle::class,
    'Example' => App\Http\Middleware\Example::class,
    'Stop' => App\Http\Middleware\Stop::class,
    'GroupExample' => [
        Shy\Http\Middleware\CSRF::class,
        Shy\Http\Middleware\IpWhitelist::class,
    ]
];
