<?php

return [
    /*
     * Framework command
     */
    'list' => [Shy\Command\Shy::class => 'list'],
    'version' => [Shy\Command\Shy::class => 'version'],
    'env' => [Shy\Command\Shy::class => 'env'],
    'show_route_index' => [Shy\Command\Shy::class => 'showRouteIndex'],
    'http_workerman' => [Shy\Command\Shy::class => 'httpWorkerMan'],
    'http_swoole' => [Shy\Command\Shy::class => 'httpSwoole'],
    'workerman' => [Shy\Command\Shy::class => 'workerMan'],
    'swoole' => [Shy\Command\Shy::class => 'swoole'],

    /*
     * Customer command
     */
    'test' => [App\Command\Example::class => 'test'],
    'test2' => [App\Command\Example::class => 'test'],
];
