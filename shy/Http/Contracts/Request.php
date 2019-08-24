<?php

namespace Shy\Http\Contracts;

interface Request
{
    /**
     * Initialize request.
     *
     * @param array $query $_GET
     * @param array $request $_POST
     * @param array $cookies $_COOKIE
     * @param array $files $_FILES
     * @param array $server $_SERVER
     * @param string $content php://input
     */
    public function initialize(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null);

    /**
     * Is request initialized.
     *
     * @return bool
     */
    public function isInitialized();

    /**
     * Set is initialized false.
     */
    public function setInitializedFalse();

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
     * @return \Shy\Http\Bags\ServerBag
     */
    public function server();

    /**
     * @return \Shy\Http\Bags\HeaderBag
     */
    public function headers();

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
     * @throws \Exception
     */
    public function getHttpHost();

    /**
     * Gets the scheme and HTTP host.
     *
     * @return string
     * @throws \Exception
     */
    public function getSchemeAndHttpHost();

    /**
     * Returns the requested URI (path and query string).
     *
     * @return string The raw URI (i.e. not URI decoded)
     */
    public function getRequestUri();

    /**
     * Return the root URL
     *
     * @return string
     * @throws \Exception
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
     * @throws \Exception
     */
    public function getUri();

    /**
     * Get the URL (no query string) for the request.
     *
     * @return string
     * @throws \Exception
     */
    public function getUrl();

    /**
     * Get client IPs
     *
     * @return array
     */
    public function getClientIps();

    /**
     * Get Method
     *
     * @return null|string
     */
    public function getMethod();

    /**
     * Is Ajax
     *
     * @return bool
     */
    public function ajax();

    /**
     * Is Pjax
     *
     * @return bool
     */
    public function pjax();

    /**
     * Expects Json
     *
     * @return bool
     */
    public function expectsJson();

    /**
     * User Agent
     *
     * @return null|string|string[]
     */
    public function userAgent();

}
