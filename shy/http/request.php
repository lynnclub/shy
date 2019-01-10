<?php

/**
 * Shy Framework Request
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http;

use shy\http\bag\ParameterBag;
use shy\http\bag\FileBag;
use shy\http\bag\ServerBag;
use shy\http\bag\HeaderBag;
use Exception;

class request
{
    /**
     * $_POST
     *
     * @var ParameterBag $request
     */
    protected $request;

    /**
     * $_GET
     *
     * @var ParameterBag $query
     */
    protected $query;

    /**
     * $_COOKIE
     *
     * @var ParameterBag $cookies
     */
    protected $cookies;

    /**
     * $_FILES
     *
     * @var FileBag $files
     */
    protected $files;

    /**
     * $_SERVER
     *
     * @var ServerBag $server
     */
    protected $server;

    /**
     * Json or Stream
     *
     * @var string
     */
    protected $content;

    /**
     * Http Header
     *
     * @var HeaderBag $headers
     */
    protected $headers;

    /**
     * Get or Post
     *
     * @var $method
     */
    protected $method;

    /**
     * Base Url Path
     *
     * @var string
     */
    protected $uri;

    /**
     * Request constructor.
     *
     * @param array $query $_GET
     * @param array $request $_POST
     * @param array $cookies $_COOKIE
     * @param array $files $_FILES
     * @param array $server $_SERVER
     * @param string $content php://input
     */
    public function init(array $query = [], array $request = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        $this->request = new ParameterBag($request);
        $this->query = new ParameterBag($query);
        $this->cookies = new ParameterBag($cookies);
        //$this->files = new FileBag($files);
        $this->server = new ServerBag($server);
        $this->headers = new HeaderBag($this->server->getHeaders());
        $this->content = $content;

        $this->method = null;
        $this->uri = null;
    }

    /**
     * Get a parameter
     *
     * @param $key
     * @param null $default
     * @return mixed|null
     */
    public function get($key, $default = null)
    {
        if ($this !== $result = $this->query->get($key, $this)) {
            return $result;
        }

        if ($this !== $result = $this->request->get($key, $this)) {
            return $result;
        }

        return $default;
    }

    /**
     * Get all parameters
     *
     * @return array
     */
    public function all()
    {
        $query = $this->query->all();
        $request = $this->request->all();

        return array_merge($query, $request);
    }

    /**
     * Is Https On
     *
     * @return bool
     */
    public function isSecure()
    {
        $https = $this->server->get('HTTPS');

        return !empty($https) && 'off' !== strtolower($https);
    }

    /**
     * Gets the request's scheme.
     *
     * @return string
     */
    public function getScheme()
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Get Port
     *
     * @return int
     */
    public function getPort()
    {
        if (!$host = $this->headers->get('HOST')) {
            return $this->server->get('SERVER_PORT');
        }

        if ('[' === $host[0]) {
            $pos = strpos($host, ':', strrpos($host, ']'));
        } else {
            $pos = strrpos($host, ':');
        }

        if (false !== $pos) {
            return (int)substr($host, $pos + 1);
        }

        return 'https' === $this->getScheme() ? 443 : 80;
    }

    /**
     * Returns the host name.
     *
     * @return string
     *
     * @throws Exception when the host name is invalid or not trusted
     */
    public function getHost()
    {
        if (!$host = $this->headers->get('HOST')) {
            if (!$host = $this->server->get('SERVER_NAME')) {
                $host = $this->server->get('SERVER_ADDR', '');
            }
        }

        // trim and remove port number from host
        // host is lowercase as per RFC 952/2181
        $host = strtolower(preg_replace('/:\d+$/', '', trim($host)));

        // as the host can come from the user (HTTP_HOST and depending on the configuration, SERVER_NAME too can come from the user)
        // check that it does not contain forbidden characters (see RFC 952 and RFC 2181)
        // use preg_replace() instead of preg_match() to prevent DoS attacks with long host names
        if ($host && '' !== preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host)) {
            throw new Exception(sprintf('Invalid Host "%s".', $host));
        }

        return $host;
    }

    /**
     * Returns the HTTP host being requested.
     *
     * @return string
     * @throws Exception
     */
    public function getHttpHost()
    {
        $scheme = $this->getScheme();
        $port = $this->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            return $this->getHost();
        }

        return $this->getHost() . ':' . $port;
    }

    /**
     * Gets the scheme and HTTP host.
     *
     * @return string
     * @throws Exception
     */
    public function getSchemeAndHttpHost()
    {
        return $this->getScheme() . '://' . $this->getHttpHost();
    }

    /**
     * Get Base Url Path
     *
     * @return string|null
     */
    public function getUri()
    {
        if (isset($this->uri)) {
            return $this->uri;
        }

        $pathString = $this->server->get('REQUEST_URI');
        $path_param = explode('?', $pathString);
        if (!empty($path_param[0])) {
            if (IS_CLI) {
                $this->uri = $path_param[0];
            } else {
                $path_param[0] = str_replace('/', DIRECTORY_SEPARATOR, $this->server->get('DOCUMENT_ROOT') . $path_param[0]);
                $path_param = str_ireplace(config('public', 'path'), '', $path_param[0]);
                $path_param = str_replace(DIRECTORY_SEPARATOR, '/', $path_param);
                $this->uri = '/' . $path_param;
            }
        }

        return $this->uri;
    }

    /**
     * Get Base Url
     *
     * @return string
     * @throws Exception
     */
    public function getBaseUrl()
    {
        $path = '/';
        if (!IS_CLI) {
            $DOCUMENT_ROOT = str_replace('/', DIRECTORY_SEPARATOR, $this->server->get('DOCUMENT_ROOT'));
            $path = str_ireplace($DOCUMENT_ROOT, '', config('public', 'path'));
            $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);
        }

        return $this->getSchemeAndHttpHost() . $path;
    }

    /**
     * Get Method
     *
     * @return null|string
     */
    public function getMethod()
    {
        if (null === $this->method) {
            $this->method = strtoupper($this->server->get('REQUEST_METHOD', 'GET'));
        }

        return $this->method;
    }

    /**
     * Is Ajax
     *
     * @return bool
     */
    public function ajax()
    {
        return 'XMLHttpRequest' == $this->headers->get('X-Requested-With');
    }

    /**
     * Is Pjax
     *
     * @return bool
     */
    public function pjax()
    {
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Expects Json
     *
     * @return bool
     */
    public function expectsJson()
    {
        return $this->ajax() && !$this->pjax();
    }

    /**
     * User Agent
     *
     * @return null|string|string[]
     */
    public function userAgent()
    {
        return $this->headers->get('User-Agent');
    }

    /**
     * Is Resource
     */
    public function isResource()
    {
        if (preg_match('/.+\.(css|ico|gif|jpg|jpeg|bmp|png)$/i', $this->getUri())) {
            return true;
        }

        return false;
    }

}