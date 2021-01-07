<?php

return [
    /*
     * Framework command
     */
    'list' => [Shy\Command\Shy::class => 'commandList'],
    'env' => [Shy\Command\Shy::class => 'env'],
    'show_route_index' => [Shy\Command\Shy::class => 'showRouteIndex'],
    'http_workerman' => [Shy\Command\Shy::class => 'httpWorkerMan'],
    'workerman' => [Shy\Command\Shy::class => 'workerMan'],

    /*
     * Customer command
     */
    'test' => [App\Command\Example::class => 'test'],
    'test2' => [App\Command\Example::class => 'test'],
];
