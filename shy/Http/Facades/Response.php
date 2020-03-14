<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contracts\Response as ResponseContracts;

/**
 * Class Response
 * @package Shy\Http\Facades
 *
 * @method static initialize()
 * @method static Response set($response)
 * @method static Response setCode(int $code)
 * @method static Response setHeader($header)
 * @method static send($view = null)
 */
class Response extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(ResponseContracts::class);
    }
}
