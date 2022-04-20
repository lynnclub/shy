<?php

namespace Shy\Http\Facade;

use Shy\Facade;
use Shy\Http\Contract\Session as SessionContract;

/**
 * Class Session
 * @package Shy\Http\Facade
 *
 * @method static bool sessionStart()
 * @method static string sessionId()
 * @method static bool exist(string $key)
 * @method static bool|mixed get(string $key)
 * @method static set(string $key, $val)
 * @method static delete(string $key)
 * @method static string token(string $name)
 */
class Session extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(SessionContract::class);
    }
}
