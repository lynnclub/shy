<?php

return [
    /**
     * http in socket
     */

    'http' => [
        'port' => 2348,
        'worker' => 2
    ],

    /**
     * socket
     */

    'socket' => [
        'chat' => [
            'address' => 'websocket://127.0.0.1:2349',
            'worker' => 2,
            'onConnect' => [App\Socket\Example::class => 'onConnect'],
            'onMessage' => [App\Socket\Example::class => 'onMessage'],
            'onClose' => [App\Socket\Example::class => 'onClose']
        ],
    ]
];
