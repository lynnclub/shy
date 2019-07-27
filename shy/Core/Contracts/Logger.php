<?php

namespace Shy\Core\Contracts;

use Psr\Log\LoggerInterface;
use Shy\Http\Contracts\Request;

interface Logger extends LoggerInterface
{
    /**
     * Logger constructor.
     *
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Config $config, Request $request = null);

    /**
     * Set request
     *
     * @param Request $request
     */
    public function setRequest(Request $request);

}
