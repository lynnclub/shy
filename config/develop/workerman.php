<?php
/**
 * WorkerMan
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

return [

    /*
    | http in socket
    */

    'http' => [
        'port' => 2348,
        'worker' => 2
    ],

    /*
    | socket
    */

    'socket' => [
        'chat' => [
            'address' => 'websocket://127.0.0.1:2349',
            'worker' => 2,
            'onConnect' => [app\socket\example::class => 'onConnect'],
            'onMessage' => [app\socket\example::class => 'onMessage'],
            'onClose' => [app\socket\example::class => 'onClose']
        ],
    ]

];
