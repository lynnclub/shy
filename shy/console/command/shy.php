<?php

/**
 * Shy command
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\console\command;

class shy
{
    public function list()
    {
        $list = shy('console')->getCommandList();
        return implode(PHP_EOL, $list);
    }

    public function version()
    {
        return 'Shy Framework v0.1' .
            PHP_EOL . 'The mini framework' .
            PHP_EOL . '( *^_^* )';
    }
}
