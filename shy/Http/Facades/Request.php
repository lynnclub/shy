<?php

namespace Shy\Http\Facades;

use Shy\Core\Facade;
use Shy\Http\Contract\Request as RequestContracts;

/**
 * Class Request
 * @package Shy\Http\Facades
 *
 * @method static initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
 * @method static bool isInitialized()
 * @method static mixed get($key, $default = null)
 * @method static array all()
 * @method static string content()
 * @method static string getHost()
 * @method static string getBaseUrl()
 * @method static string getPathInfo()
 * @method static string getUri()
 * @method static string getUrl()
 * @method static array getClientIps()
 * @method static string getMethod()
 * @method static bool isXmlHttpRequest()
 * @method static bool isPjax()
 * @method static bool expectsJson()
 * @method static string|null header($key, $default = null)
 * @method static string|null server($key, $default = null)
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
