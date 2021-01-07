#!/usr/bin/env php
<?php

/**
 * @var $container \Shy\Core\Container
 */
$container = require __DIR__ . '/bootstrap/command.php';

$container->make(\Shy\Command::class)->run();
