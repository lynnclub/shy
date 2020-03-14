<?php

return [
    /*
     * Framework command
     */
    'list' => [Shy\Command\Shy::class => 'list'],
    'version' => [Shy\Command\Shy::class => 'version'],
    'env' => [Shy\Command\Shy::class => 'env'],
    'delete_config_cache_file' => [Shy\Command\Shy::class => 'deleteConfigCacheFile'],
    'http' => [Shy\Command\Shy::class => 'http'],
    'workerman' => [Shy\Command\Shy::class => 'workerMan'],

    /*
     * Customer command
     */
    'test' => [App\Command\Example::class => 'test'],
    'test2' => [App\Command\Example::class => 'test'],
];
