<?php

namespace Shy\Http\Facade;

use Shy\Core\Facade;
use Shy\Http\Contract\Response as ResponseContracts;

/**
 * Class Response
 * @package Shy\Http\Facade
 *
 * @method static initialize()
 * @method static Response withStatus(int $code)
 * @method static Response withHeader($name, $value = '')
 * @method static Response withHeaders(array $headers)
 * @method static output($view = null)
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
