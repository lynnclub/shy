#!/usr/bin/env php
<?php

/**
 * @var $container \Shy\Core\Container
 */
$container = require __DIR__ . '/bootstrap/command.php';

// Hook
\Shy\Core\Facades\Hook::run('command_before');

$container->make(\Shy\Command::class)->run();

// Hook
\Shy\Core\Facades\Hook::run('command_after');
