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
     * 获取参数值
     * Get param
     *
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public function get(string $key, $default = null);

    /**
     * 获取客户端IP
     * Returns the client IP addresses.
     *
     * @return array
     */
    public function getClientIps();

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
     * Returns the root URL from which this request is executed.
     *
     * The base URL never ends with a /.
     *
     * This is similar to getBasePath(), except that it also includes the
     * script filename (e.g. index.php) if one exists.
     *
     * @return string The raw URL (i.e. not urldecoded)
     */
    public function getBaseUrl();

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme();

    /**
     * Returns the port on which the request is made.
     *
     * This method can read the client port from the "X-Forwarded-Port" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Port" header must contain the client port.
     *
     * @return int|string|null Can be a string if fetched from the server bag
     */
    public function getPort();

    /**
     * Returns the HTTP host being requested.
     *
     * The port name will be appended to the host if it's non-standard.
     *
     * @return string
     */
    public function getHttpHost();

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri();

    /**
     * Gets the scheme and HTTP host.
     *
     * If the URL was called with basic authentication, the user
     * and the password are not added to the generated string.
     *
     * @return string
     */
    public function getSchemeAndHttpHost();

    /**
     * Generates a normalized URI (URL) for the Request.
     *
     * @return string
     *
     * @see getQueryString()
     */
    public function getUri();

    /**
     * Generates a normalized URI for the given path.
     *
     * @param string $path A path to use instead of the current one
     *
     * @return string
     */
    public function getUriForPath(string $path);

    /**
     * Generates the normalized query string for the Request.
     *
     * It builds a normalized query string, where keys/value pairs are alphabetized
     * and have consistent escaping.
     *
     * @return string|null
     */
    public function getQueryString();

    /**
     * Checks whether the request is secure or not.
     *
     * This method can read the client protocol from the "X-Forwarded-Proto" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Proto" header must contain the protocol: "https" or "http".
     *
     * @return bool
     */
    public function isSecure();

    /**
     * Returns the host name.
     *
     * This method can read the client host name from the "X-Forwarded-Host" header
     * when trusted proxies were set via "setTrustedProxies()".
     *
     * The "X-Forwarded-Host" header must contain the client host name.
     *
     * @return string
     *
     * @throws \Exception when the host name is invalid or not trusted
     */
    public function getHost();

    /**
     * Gets the request "intended" method.
     *
     * If the X-HTTP-Method-Override header is set, and if the method is a POST,
     * then it is used to determine the "real" intended HTTP method.
     *
     * The _method request parameter can also be used to determine the HTTP method,
     * but only if enableHttpMethodParameterOverride() has been called.
     *
     * The method is always an uppercased string.
     *
     * @return string
     *
     * @see getRealMethod()
     */
    public function getMethod();

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource
     */
    public function getContent(bool $asResource = false);

    /**
     * Returns true if the request is an XMLHttpRequest.
     *
     * It works if your JavaScript library sets an X-Requested-With HTTP header.
     * It is known to work with common JavaScript frameworks:
     *
     * @see https://wikipedia.org/wiki/List_of_Ajax_frameworks#JavaScript
     *
     * @return bool
     */
    public function isXmlHttpRequest();

    /**
     * 是否初始化
     *
     * @return bool
     */
    public function isInitialized();

    /**
     * 获取所有参数
     * Get all params.
     *
     * @return array
     */
    public function all();

    /**
     * 是否Pjax请求
     * Is Pjax request
     *
     * @return bool
     */
    public function isPjax();

    /**
     * 是否期望响应json
     * Is expects response json
     *
     * @return bool
     */
    public function expectsJson();

    /**
     * 获取请求头信息
     * Get header info
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function header(string $key, string $default = null);

    /**
     * 获取服务器信息
     * Get server info
     *
     * @param string $key
     * @param string|null $default
     * @return string|null
     */
    public function server(string $key, string $default = null);
}
