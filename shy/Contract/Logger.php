<?php

namespace Shy\Contract;

use Psr\Log\LoggerInterface;
use Shy\Http\Contract\Request;

interface Logger extends LoggerInterface
{
    /**
     * Logger constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request = null);

    /**
     * Set request
     *
     * @param Request $request
     */
    public function setRequest(Request $request);
}
