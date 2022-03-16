#!/usr/bin/env php
<?php

/**
 * @var $container \Shy\Container
 */
$container = require __DIR__ . '/bootstrap/command.php';

// Hook
\Shy\Facade\Hook::run('command_before');

$container->make(\Shy\Command::class)->run();

// Hook
\Shy\Facade\Hook::run('command_after');
