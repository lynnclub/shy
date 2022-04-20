#!/usr/bin/env php
<?php

use Shy\Command;
use Shy\Facade\Hook;

/**
 * 执行启动器，得到容器
 *
 * @var $container \Shy\Container
 */
$container = require __DIR__ . '/bootstrap/command.php';

// 钩子-命令处理前
Hook::run('command_before');

$container->make(Command::class)->run();

// 钩子-命令处理后
Hook::run('command_after');
