<?php

namespace Shy\Contract;

use Psr\Log\LoggerInterface;
use Shy\Http\Contract\Request;

interface Logger extends LoggerInterface
{
    /**
     * Logger constructor.
     *
     * @param Request|null $request
     */
    public function __construct(Request $request = null);

    /**
     * 设置请求
     * Set request
     *
     * @param Request|null $request
     */
    public function setRequest(Request $request = null);
}
