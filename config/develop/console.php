<?php

return [
    /*
     * Framework command
     */
    'list' => [Shy\Console\Command\Shy::class => 'list'],
    'version' => [Shy\Console\Command\Shy::class => 'version'],
    'http' => [Shy\Console\Command\Shy::class => 'http'],
    'workerMan' => [Shy\Console\Command\Shy::class => 'workerMan'],

    /*
     * Customer command
     */
    'test' => [App\Console\Command\Example::class => 'test'],
    'test2' => [App\Console\Command\Example::class => 'test'],
];
