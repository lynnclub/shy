<?php

/**
 * Shy Framework Router
 *
 * @author    lynn<admin@lynncho.cn>
 * @link      http://lynncho.cn/
 */

namespace shy\http;

use RuntimeException;

class response
{
    private $code;

    private $header;

    private $response;

    /**
     * Set Response
     *
     * @param $response
     * @return $this
     */
    public function set($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Set Http Code
     *
     * @param int $code
     * @return $this
     */
    public function setCode(int $code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Set Http Header
     *
     * @param array $header
     * @return $this
     */
    public function setHeader(array $header)
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Send Response
     *
     * @param view|string $view
     */
    public function send($view = null)
    {
        if ($this->code) {
            header($this->httpCodeMessage($this->code));
        }
        if ($this->header) {
            foreach ($this->header as $key => $value) {
                header($key, $value);
            }
        }

        if ($view) {
            $this->response = $view;
        }
        if ($this->response instanceof view) {
            echo $this->response->render();
        } elseif (is_string($this->response)) {
            echo $this->response;
        } else {
            throw new RuntimeException('Invalid response');
        }
    }

    /**
     * Http Code Message
     *
     * @param int $code
     * @return string
     */
    public function httpCodeMessage(int $code)
    {
        switch ($code) {
            case 300:
                $msg = 'HTTP/1.1 300 Multiple Choices.';
                break;
            case 301:
                $msg = 'HTTP/1.1 301 Moved Permanently.';
                break;
            case 302:
                $msg = 'HTTP/1.1 302 Found.';
                break;
            case 303:
                $msg = 'HTTP/1.1 303 See Other.';
                break;
            case 304:
                $msg = 'HTTP/1.1 304 Not Modified.';
                break;
            case 305:
                $msg = 'HTTP/1.1 305 Use Proxy.';
                break;
            case 306:
                $msg = 'HTTP/1.1 306 Unused.';
                break;
            case 307:
                $msg = 'HTTP/1.1 307 Temporary Redirect.';
                break;
            case 400:
                $msg = 'HTTP/1.1 400 Bad Request.';
                break;
            case 401:
                $msg = 'HTTP/1.1 401 Unauthorized';
                break;
            case 403:
                $msg = 'HTTP/1.1 403 Forbidden.';
                break;
            case 404:
                $msg = 'HTTP/1.1 404 Not Found.';
                break;
            case 405:
                $msg = 'HTTP/1.1 405 Method Not Allowed.';
                break;
            case 408:
                $msg = 'HTTP/1.1 408 Request Timeout.';
                break;
            case 409:
                $msg = 'HTTP/1.1 409 Conflict.';
                break;
            case 500:
                $msg = 'HTTP/1.1 500 Internal Server Error.';
                break;
            case 502:
                $msg = 'HTTP/1.1 502 Bad Gateway.';
                break;
            case 503:
                $msg = 'HTTP/1.1 503 Service Unavailable.';
                break;
            case 504:
                $msg = 'HTTP/1.1 504 Gateway Timeout.';
                break;
            default:
                $msg = 'HTTP/1.1 500 Http Code Error.';
        }

        return $msg;
    }
}