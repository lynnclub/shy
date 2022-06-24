<?php

namespace Shy\Http\Facade;

use Shy\Facade;
use Shy\Http\Contract\Session as SessionContract;

/**
 * Class Session
 * @package Shy\Http\Facade
 *
 * @method static SessionContract init(array $config = [])
 * @method static string sessionId(string $id = '')
 * @method static bool has(string $key)
 * @method static mixed|false get(string $key)
 * @method static bool set(string $key, $data)
 * @method static string token(string $key)
 * @method static delete(string $key)
 * @method static bool close()
 * @method static bool destroy()
 */
class Session extends Facade
{
    /**
     * 获取实例
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(SessionContract::class);
    }
}
