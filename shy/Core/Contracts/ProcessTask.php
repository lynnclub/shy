<?php

namespace Shy\Core\Contracts;

interface ProcessTask
{
    /**
     * Run task
     *
     * @return void
     */
    public function run();
}
