<?php

return [
    'IpWhitelist' => Shy\Http\Middleware\IpWhitelist::class,
    'Throttle' => Shy\Http\Middleware\Throttle::class,
    'Example' => App\Http\Middleware\Example::class,
    'GroupExample' => [
        Shy\Http\Middleware\Csrf::class,
    ]
];
