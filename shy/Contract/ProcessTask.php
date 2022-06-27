<?php

namespace Shy\Contract;

interface ProcessTask
{
    /**
     * 运行任务
     * Run task
     *
     * @return void
     */
    public function run();
}
