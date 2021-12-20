<?php

namespace Shy\Http\Contract;

use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\ServerBag;

/**
 * Interface Request
 *
 * @package Shy\Http\Contract
 * @property ParameterBag $request
 * @property ParameterBag $query
 * @property ServerBag $server
 * @property FileBag $files
 * @property ParameterBag $cookies
 * @property HeaderBag $headers
 */
interface Request
{
    /**
     * Sets the parameters for this request.
     *
     * This method also re-initializes all properties.
     *
     * @param array $query The GET parameters
     * @param array $request The POST parameters
     * @param array $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array $cookies The COOKIE parameters
     * @param array $files The FILES parameters
     * @param array $server The SERVER parameters
     * @param string|resource|null $content The raw body data
     */
    public function initialize(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null);

    /**
     * @return bool
     */
    public function isInitialized();

    /**
     * Get a parameter.
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null);

    /**
     * Get all parameters.
     *
     * @return array
     */
    public function all();

    /**
     * Get php://input
     */
    public function content();

    /**
     * Is HTTPS on.
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme();

    /**
     * Get port.
     *
     * @return int
     */
    public function getPort();

    /**
     * Returns the host name.
     *
     * @return string
     *
     * @throws \Exception when the host name is invalid or not trusted
     */
    public function getHost();

    /**
     * Returns the HTTP host being requested.
     *
     * @return string
     */
    public function getHttpHost();

    /**
     * Gets the scheme and HTTP host.
     *
     * @return string
     */
    public function getSchemeAndHttpHost();

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri();

    /**
     * Returns the root URL from which this request is executed.
     *
     * @return string
     */
    public function getBaseUrl();

    /**
     * Returns the path being requested relative to the executed script.
     *
     * The path info always starts with a /.
     *
     * Suppose this request is instantiated from /mysite on localhost:
     *
     *  * http://localhost/mysite              returns an empty string
     *  * http://localhost/mysite/about        returns '/about'
     *  * http://localhost/mysite/enco%20ded   returns '/enco%20ded'
     *  * http://localhost/mysite/about?var=1  returns '/about'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getPathInfo();

    /**
     * Returns the root path from which this request is executed.
     *
     * Suppose that an index.php file instantiates this request object:
     *
     *  * http://localhost/index.php         returns an empty string
     *  * http://localhost/index.php/page    returns an empty string
     *  * http://localhost/web/index.php     returns '/web'
     *  * http://localhost/we%20b/index.php  returns '/we%20b'
     *
     * @return string The raw path (i.e. not urldecoded)
     */
    public function getBasePath();

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string
     */
    public function getUri();

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     */
    public function getUrl();

    /**
     * Returns the client IP addresses.
     *
     * @return array
     */
    public function getClientIps();

    /**
     * Gets the request "intended" method.
     *
     * @return string
     */
    public function getMethod();

    /**
     * Returns TRUE if the request is a XMLHttpRequest.
     *
     * @return bool
     */
    public function isXmlHttpRequest();

    /**
     * Is Pjax
     *
     * @return bool
     */
    public function isPjax();

    /**
     * Expects Json
     *
     * @return bool
     */
    public function expectsJson();

    /**
     * Get Header
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function header($key, $default = null);

    /**
     * Get Server
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function server($key, $default = null);
}
