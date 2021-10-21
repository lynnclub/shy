<?php

return [
    /**
     * Framework command
     */

    'list' => [Shy\Command\Shy::class => 'commandList'],
    'env' => [Shy\Command\Shy::class => 'env'],
    'route' => [Shy\Command\Shy::class => 'routeConfig'],
    'route_index' => [Shy\Command\Shy::class => 'routeIndex'],
    'workerman' => [Shy\Command\WorkerMan::class => 'workerMan'],
    'http_workerman' => [Shy\Command\WorkerMan::class => 'httpWorkerMan'],

    /**
     * Customer command
     */

    'test' => [App\Command\Example::class => 'test'],
    'test2' => [App\Command\Example::class => 'test'],
];
