<?php

namespace Shy\Http\Facade;

use Shy\Facade;
use Shy\Http\Contract\Response as ResponseContract;

/**
 * Class Response
 * @package Shy\Http\Facade
 *
 * @method static Response withStatus(int $code)
 * @method static Response withHeader($name, $value = '')
 * @method static Response withHeaders(array $headers)
 * @method static output($view = null)
 * @method static initialize()
 */
class Response extends Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(ResponseContract::class);
    }
}
