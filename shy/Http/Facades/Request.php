<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contracts\Request as RequestContracts;

/**
 * Class Request
 * @package Shy\Http\Facades
 *
 * @method static initialize(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
 * @method static bool isInitialized()
 * @method static setInitializedFalse()
 * @method static mixed get($key, $default = null)
 * @method static array all()
 * @method static array server()
 * @method static array headers()
 * @method static php://input content()
 * @method static string getHost()
 * @method static string getBaseUrl()
 * @method static string getPathInfo()
 * @method static string getUri()
 * @method static string getUrl()
 * @method static array getClientIps()
 * @method static string getMethod()
 * @method static bool ajax()
 * @method static bool pjax()
 * @method static null|string|string[] userAgent()
 */
class Request extends Facade
{
    /**
     * Get the instance.
     *
     * @return object
     */
    protected static function getInstance()
    {
        return shy(RequestContracts::class);
    }
}
